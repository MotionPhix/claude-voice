<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\SmsService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    public function __construct(
        private SmsService $sms_service
    ) {}

    protected int $otpTtlMinutes = 5;

    public function create(): Response
    {
        return Inertia::render('auth/Register');
    }

    public function store(Request $request)
    {
        // Use a custom validation rule that checks the formatted phone for uniqueness
        $request->validate(
            [
                'name' => ['required', 'string', 'max:255'],
                'phone' => [
                    'required',
                    'phone:mobile,MW',
                    function ($attribute, $value, $fail) {
                        // Format the phone to E.164 and check if it already exists
                        try {
                            $formatted = phone($value, 'MW')->formatE164();
                            if (User::where('phone', $formatted)->exists()) {
                                $fail('This phone number has already been registered.');
                            }
                        } catch (\Exception $e) {
                            $fail('Invalid phone number format.');
                        }
                    },
                ],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            ]
        );

        // Format phone number for database storage
        $phone = phone($request->phone, 'MW')->formatE164();

        $rateKey = "register-otp:$phone";

        if (RateLimiter::tooManyAttempts($rateKey, 3)) {
            $seconds = RateLimiter::availableIn($rateKey);

            return back()->withErrors([
                'phone' => "Too many attempts. Try again in {$seconds} seconds.",
            ]);
        }

        RateLimiter::hit($rateKey, 120);

        try {
            $user = User::create([
                'name' => $request->name,
                'phone' => $phone,
                'email' => $request->email,
                'phone_verified_at' => null,
            ]);
        } catch (QueryException $e) {
            // Handle race condition: phone or email already exists
            if ($e->getCode() === '23000') {
                return back()->withErrors([
                    'phone' => 'This phone number has already been registered. Please try again or login.',
                ])->withInput();
            }

            throw $e;
        }

        $otp = random_int(100000, 999999);

        Cache::put(
            "otp-register:$phone",
            [
                'hash' => Hash::make($otp),
                'attempts' => 0,
                'expires_at' => now()->addMinutes($this->otpTtlMinutes)->timestamp,
            ],
            now()->addMinutes($this->otpTtlMinutes)
        );

        /*try {
            $client = new Client(
                config('services.twilio.sid'),
                config('services.twilio.token')
            );

            $client->messages->create($phone, [
                'from' => config('services.twilio.from'),
                'body' => "Your verification code is {$otp}\n\n@{config('app.name')}.com"
            ]);
        } catch (\Exception $e) {
            $user->delete();
            Cache::forget("otp-register:$phone");

            report($e);

            return back()->withErrors([
                'phone' => 'Failed to send verification code. Try again.'
            ]);
        }*/

        if (! $this->sms_service->sendOtp($phone, $otp)) {
            $user->delete();

            Cache::forget("otp-register:$phone");

            return back()->withErrors([
                'phone' => 'Failed to send verification code. Try again later.',
            ]);
        }

        $token = Str::uuid()->toString();

        Cache::put(
            "register-token:$token",
            $phone,
            now()->addMinutes($this->otpTtlMinutes)
        );

        return to_route('verify.otp.form', [
            'token' => $token,
        ]);
    }
}
