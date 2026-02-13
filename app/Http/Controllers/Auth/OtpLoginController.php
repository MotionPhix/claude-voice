<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use App\Models\RememberedDevice;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;
use App\Services\SmsService;

class OtpLoginController extends Controller
{
    public function __construct(
        private SmsService $sms_service
    ) {}

    protected int $otpTtlMinutes = 5;
    protected int $maxAttempts = 5;

    public function showPhoneForm()
    {
        return inertia('auth/PhoneLogin');
    }

    public function sendOtp(Request $request)
    {
        $request->validate([
            'phone' => ['required', 'phone:mobile,MW'],
        ], [
            'phone.phone' => 'Please enter a valid mobile phone number.',
        ]);

        $phone = phone($request->phone, 'MW')->formatE164();

        $rateKey = "otp-send:$phone";

        if (RateLimiter::tooManyAttempts($rateKey, 3)) {
            $seconds = RateLimiter::availableIn($rateKey);

            return back()->withErrors([
                'phone' => "Too many attempts. Try again in {$seconds} seconds."
            ]);
        }

        RateLimiter::hit($rateKey, 120);

        // Prevent re-sending if OTP still valid (COST OPTIMIZATION)
        if (Cache::has("otp:$phone")) {
            return back()->with('status', 'OTP already sent. Please check your phone.');
        }

        if ($cookie = $request->cookie('remember_device')) {

            $user = User::where('phone', $phone)->first();

            if ($user) {

                $device = RememberedDevice::where('user_id', $user->id)
                    ->where('expires_at', '>', now())
                    ->get()
                    ->first(fn ($d) => Hash::check($cookie, $d->token_hash));

                if ($device) {
                    Auth::login($user, true);
                    return redirect()->route('dashboard');
                }
            }
        }

        $otp = random_int(100000, 999999);

        $fingerprint = $this->fingerprint($request);

        Cache::put(
            "otp:$phone",
            [
                'hash' => Hash::make($otp),
                'attempts' => 0,
                'device' => $fingerprint,
                'expires_at' => now()->addMinutes($this->otpTtlMinutes)->timestamp,
            ],
            now()->addMinutes($this->otpTtlMinutes)
        );

        User::firstOrCreate(['phone' => $phone]);

        /*try {
            $client = new Client(
                config('services.twilio.sid'),
                config('services.twilio.token')
            );

            $client->messages->create($phone, [
                'from' => config('services.twilio.from'),
                'body' => "Your login code is {$otp}\n\n@yourdomain.com #{$otp}"
            ]);
        } catch (\Exception $e) {
            Cache::forget("otp:$phone");
            report($e);

            return back()->withErrors([
                'phone' => 'Failed to send OTP. Try again.'
            ]);
        }*/

        if (!$this->sms_service->sendOtp($phone, $otp)) {
            Cache::forget("otp:$phone");

            return back()->withErrors([
                'phone' => 'Failed to send login code. Try again later.'
            ]);
        }

        $loginToken = Str::uuid()->toString();

        Cache::put(
            "login-token:$loginToken",
            $phone,
            now()->addMinutes($this->otpTtlMinutes)
        );

        return to_route('verify.otp.form', [
            'token' => $loginToken
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'token' => ['required'],
            'otp' => ['required', 'digits:6'],
        ]);

        $phone = Cache::get("login-token:{$request->token}");

        if (!$phone) {
            return redirect()->route('login')
                ->withErrors(['otp' => 'Session expired.']);
        }

        $data = Cache::get("otp:$phone");

        if (!$data) {
            return back()->withErrors(['otp' => 'OTP expired.']);
        }

        // Expiry check (prevents TTL extension abuse)
        if (now()->timestamp > $data['expires_at']) {
            Cache::forget("otp:$phone");
            return back()->withErrors(['otp' => 'OTP expired.']);
        }

        // Device binding check
        if ($data['device'] !== $this->fingerprint($request)) {
            Cache::forget("otp:$phone");
            Cache::forget("login-token:{$request->token}");
            abort(403, 'Device mismatch.');
        }

        if ($data['attempts'] >= $this->maxAttempts) {
            Cache::forget("otp:$phone");
            return back()->withErrors(['otp' => 'Too many attempts.']);
        }

        if (!Hash::check($request->otp, $data['hash'])) {
            $data['attempts']++;

            Cache::put(
                "otp:$phone",
                $data,
                now()->addSeconds(
                    $data['expires_at'] - now()->timestamp
                )
            );

            return back()->withErrors(['otp' => 'Invalid OTP.']);
        }

        // Success
        Cache::forget("otp:$phone");
        Cache::forget("login-token:{$request->token}");

        $user = User::where('phone', $phone)->first();

        $user->update([
            'phone_verified_at' => now(),
        ]);

        Auth::login($user, true);

        $request->session()->regenerate();

        // If user checked remember device
        if ($request->boolean('remember_device')) {

            $rawToken = Str::random(64);

            RememberedDevice::create([
                'user_id' => $user->id,
                'token_hash' => Hash::make($rawToken),
                'user_agent' => substr($request->userAgent(), 0, 255),
                'expires_at' => now()->addDays(30),
            ]);

            Cookie::queue(
                'remember_device',
                $rawToken,
                60 * 24 * 30, // 30 days
                null,
                null,
                true,  // secure
                true,  // httpOnly
                false,
                'Strict'
            );
        }

        return redirect()->route('dashboard');
    }

    protected function fingerprint(Request $request): string
    {
        return hash('sha256', $request->ip() . $request->userAgent());
    }

    public function showVerifyForm(Request $request)
    {
        $token = $request->query('token');

        /*if (!$token || !Cache::has("login-token:$token")) {
            return redirect()->route('login')
                ->withErrors(['otp' => 'Session expired.']);
        }*/

        return inertia('auth/VerifyOtp', [
            'token' => $token,
        ]);
    }

    public function verifyRegistrationOtp(Request $request)
    {
        $request->validate([
            'token' => ['required'],
            'otp' => ['required', 'digits:6'],
        ]);

        $phone = Cache::get("register-token:{$request->token}");

        if (!$phone) {
            return to_route('register')->withErrors(['otp' => 'Session expired.']);
        }

        $data = Cache::get("otp-register:$phone");

        if (!$data || now()->timestamp > $data['expires_at']) {
            return back()->withErrors(['otp' => 'OTP expired.']);
        }

        if ($data['attempts'] >= 5) {
            return back()->withErrors(['otp' => 'Too many attempts.']);
        }

        if (!Hash::check($request->otp, $data['hash'])) {
            $data['attempts']++;

            Cache::put(
                "otp-register:$phone",
                $data,
                now()->addSeconds($data['expires_at'] - now()->timestamp)
            );

            return back()->withErrors(['otp' => 'Invalid OTP.']);
        }

        Cache::forget("otp-register:$phone");
        Cache::forget("register-token:{$request->token}");

        $user = User::where('phone', $phone)->first();

        $user->update([
            'phone_verified_at' => now(),
        ]);

        Auth::login($user);

        $request->session()->regenerate();

        $user->sendEmailVerificationNotification();

        return redirect()->route('dashboard');
    }

}
