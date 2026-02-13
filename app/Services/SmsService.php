<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    protected string $baseUrl;

    protected string $apiKey;

    protected string $appId;

    protected string $password;

    protected string $sender;

    public function __construct()
    {
        $this->baseUrl = config('services.telcomw.base_url');
        $this->apiKey = config('services.telcomw.api_key');
        $this->appId = config('services.telcomw.app_id');
        $this->password = config('services.telcomw.password');
        $this->sender = config('services.telcomw.sender');
    }

    /**
     * Send SMS via /send endpoint
     */
    public function sendSms(string $phone, string $message): bool
    {
        try {
            Log::debug('SMS Request', [
                'phone' => $phone,
                'base_url' => $this->baseUrl,
                'message' => $message,
            ]);

            $response = Http::secure()->asForm()->post(
                "{$this->baseUrl}/send",
                [
                    'api_key' => $this->apiKey,
                    'password' => $this->password,
                    'text' => $message,
                    'numbers' => $phone,
                    'from' => $this->sender,
                ]
            );

            if (! $response->successful()) {
                Log::error('SMS HTTP failure', [
                    'phone' => $phone,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return false;
            }

            $data = $response->json();

            Log::debug('SMS Response', [
                'phone' => $phone,
                'data' => $data,
            ]);

            // Check if response indicates success
            if (($data['type'] ?? null) !== 'success') {
                Log::warning('SMS Response type is not success', [
                    'phone' => $phone,
                    'response' => $data,
                ]);

                return false;
            }

            // Check if feedback array exists and has items with successful delivery
            $feedback = $data['feedback'] ?? [];

            // Handle cases where feedback is boolean false (account has no credits or SMS disabled)
            if ($feedback === false) {
                Log::critical('SMS delivery failed: Account issue', [
                    'phone' => $phone,
                    'feedback' => $feedback,
                    'reason' => 'Account may lack SMS credits, have invalid credentials, or SMS service not enabled',
                ]);

                return false;
            }

            if (! is_array($feedback) || empty($feedback)) {
                Log::warning('SMS Response feedback is empty or invalid', [
                    'phone' => $phone,
                    'feedback' => $feedback,
                    'response' => $data,
                ]);

                return false;
            }

            // Check if at least one feedback item was successfully sent
            // Status code 2 typically means "pending/sent"
            foreach ($feedback as $item) {
                if (($item['code'] ?? null) === 200 && ($item['status'] ?? null) >= 1) {
                    Log::info('SMS sent successfully', [
                        'phone' => $phone,
                        'msdn' => $item['msdn'] ?? 'unknown',
                        'id' => $item['id'] ?? 'unknown',
                    ]);

                    return true;
                }
            }

            Log::error('SMS delivery failed - no successful feedback items', [
                'phone' => $phone,
                'feedback' => $feedback,
            ]);

            return false;

        } catch (\Exception $e) {
            Log::error('SMS Exception', [
                'phone' => $phone,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return false;
        }
    }

    /**
     * Send WhatsApp via /whatsapp/text endpoint
     */
    public function sendWhatsapp(string $phone, string $message): bool
    {
        try {
            Log::debug('WhatsApp Request', [
                'phone' => $phone,
                'base_url' => $this->baseUrl,
                'message' => $message,
                'app_id' => $this->appId,
            ]);

            $response = Http::withHeaders([
                'x-api-key' => $this->apiKey,
                'x-app-id' => $this->appId,
                'Accept' => 'application/json',
            ])->post(
                "{$this->baseUrl}/whatsapp/text",
                [
                    [
                        'recipient' => $phone,
                        'message' => $message,
                    ],
                ]
            );

            if (! $response->successful()) {
                Log::error('WhatsApp HTTP failure', [
                    'phone' => $phone,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return false;
            }

            $data = $response->json();

            Log::debug('WhatsApp Response', [
                'phone' => $phone,
                'data' => $data,
            ]);

            // Check for API-level errors (404, auth issues, etc)
            if (($data['status'] ?? null) === 404 && str_contains(($data['message'] ?? ''), 'Invalid App ID')) {
                Log::critical('WhatsApp: Invalid App ID configuration', [
                    'phone' => $phone,
                    'app_id' => $this->appId,
                    'message' => $data['message'] ?? 'Unknown error',
                ]);

                return false;
            }

            $success = ($data['success'] ?? false) === true;

            if (! $success) {
                Log::warning('WhatsApp Response indicated failure', [
                    'phone' => $phone,
                    'response' => $data,
                ]);
            }

            return $success;

        } catch (\Exception $e) {
            Log::error('WhatsApp Exception', [
                'phone' => $phone,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return false;
        }
    }

    /**
     * Send OTP (tries WhatsApp first, then SMS fallback)
     */
    public function sendOtp(string $phone, string $otp): bool
    {
        $message = "Your verification code is {$otp}. Valid for 5 minutes.";

        Log::info('OTP Send Initiated', [
            'phone' => $phone,
            'otp_length' => strlen($otp),
        ]);

        // Try WhatsApp first
        if ($this->sendWhatsapp($phone, $message)) {
            Log::info('OTP sent successfully via WhatsApp', [
                'phone' => $phone,
            ]);

            return true;
        }

        Log::info('WhatsApp failed, attempting SMS fallback', [
            'phone' => $phone,
        ]);

        // Fallback to SMS
        $result = $this->sendSms($phone, $message);

        if ($result) {
            Log::info('OTP sent successfully via SMS', [
                'phone' => $phone,
            ]);
        } else {
            Log::error('OTP send failed via both WhatsApp and SMS', [
                'phone' => $phone,
            ]);
        }

        return $result;
    }

    /**
     * Check SMS account balance
     *
     * @return array{success: bool, balance?: string, raw_response?: array}
     */
    public function checkBalance(): array
    {
        try {
            Log::debug('Checking SMS Balance', [
                'api_key' => substr($this->apiKey, 0, 5).'***',
            ]);

            $response = Http::asForm()->post(
                "{$this->baseUrl}/balance",
                [
                    'api_key' => $this->apiKey,
                    'password' => $this->password,
                ]
            );

            if (! $response->successful()) {
                Log::error('Balance check HTTP failure', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return [
                    'success' => false,
                    'error' => 'HTTP request failed',
                ];
            }

            $data = $response->json();

            Log::debug('Balance check response', [
                'data' => $data,
            ]);

            // Check if response contains balance information
            if (isset($data['balance'])) {
                Log::info('SMS Balance retrieved', [
                    'balance' => $data['balance'],
                ]);

                return [
                    'success' => true,
                    'balance' => $data['balance'],
                    'raw_response' => $data,
                ];
            }

            // If balance not directly in response, check other possible keys
            if (isset($data['credit']) || isset($data['credits'])) {
                $balance = $data['credit'] ?? $data['credits'];
                Log::info('SMS Balance retrieved (alternate key)', [
                    'balance' => $balance,
                ]);

                return [
                    'success' => true,
                    'balance' => $balance,
                    'raw_response' => $data,
                ];
            }

            // If we got here, API didn't return balance in expected format
            Log::warning('Balance check returned unexpected format', [
                'response' => $data,
            ]);

            return [
                'success' => false,
                'error' => 'Unexpected API response format',
                'raw_response' => $data,
            ];

        } catch (\Exception $e) {
            Log::error('Balance check exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}
