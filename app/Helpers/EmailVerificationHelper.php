<?php

// app/Helpers/EmailVerificationHelper.php
namespace App\Helpers;

use App\Mail\VerificationCodeMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class EmailVerificationHelper
{
    /**
     * Send verification code to email
     *
     * @param string $email
     * @param string $purpose (optional) - e.g., 'registration', 'password_reset', 'login'
     * @param int $codeLength (optional) - length of verification code
     * @param int $expiryMinutes (optional) - expiry time in minutes
     * @return array
     */
    public static function sendVerificationCode(
        string $email,
        string $purpose = 'verification',
        int $codeLength = 6,
        int $expiryMinutes = 15
    ): array {
        try {
            $user = User::where('email', $email)->first();

            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'User not found',
                ];
            }

            // Generate verification code
            $code = self::generateCode($codeLength);

            // Store code in database with expiry
            $user->update([
                'otp_code' => $code,
                'otp_expires_at' => now()->addMinutes($expiryMinutes),
            ]);

            // Send email
            Mail::to($email)->send(new VerificationCodeMail($code, $purpose, $expiryMinutes));

            return [
                'success' => true,
                'message' => 'Verification code sent successfully',
                'expires_in' => $expiryMinutes
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to send verification code: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Verify the code
     *
     * @param string $email
     * @param string $code
     * @param string $purpose
     * @param int $maxAttempts
     * @return array
     */
    public static function verifyCode(
        string $email,
        string $code,
        string $purpose = 'verification',
        int $maxAttempts = 3
    ): array {
        $user = User::where('email', $email)->first();

        if (!$user || !$user->otp_code || !$user->otp_expires_at) {
            return [
                'success' => false,
                'message' => 'Verification code expired or not found'
            ];
        }

        if (now()->greaterThan($user->otp_expires_at)) {
            $user->update(['otp_code' => null, 'otp_expires_at' => null]);
            return [
                'success' => false,
                'message' => 'Verification code expired or not found'
            ];
        }

        // Verify code
        if ($user->otp_code !== $code) {
            return [
                'success' => false,
                'message' => 'Invalid verification code',
            ];
        }

        // Code is valid, clear from database
        $user->update(['otp_code' => null, 'otp_expires_at' => null]);

        return [
            'success' => true,
            'message' => 'Verification successful'
        ];
    }

    /**
     * Generate verification code
     *
     * @param int $length
     * @return string
     */
    private static function generateCode(int $length = 6): string
    {
        $code = '';
        for ($i = 0; $i < $length; $i++) {
            $code .= random_int(0, 9);
        }
        return $code;
    }

    /**
     * Check if verification code exists
     *
     * @param string $email
     * @param string $purpose
     * @return bool
     */
    public static function hasActiveCode(string $email, string $purpose = 'verification'): bool
    {
        $user = User::where('email', $email)->first();
        return $user && $user->otp_code && $user->otp_expires_at && now()->lessThan($user->otp_expires_at);
    }

    /**
     * Get remaining time for verification code
     *
     * @param string $email
     * @param string $purpose
     * @return int|null (seconds remaining)
     */
    public static function getRemainingTime(string $email, string $purpose = 'verification'): ?int
    {
        $user = User::where('email', $email)->first();

        if (!$user || !$user->otp_expires_at || now()->greaterThan($user->otp_expires_at)) {
            return null;
        }

        return max(0, now()->diffInSeconds($user->otp_expires_at));
    }
}

// Helper function (add to app/helpers.php or create new helper file)
if (!function_exists('send_verification_code')) {
    /**
     * Send verification code via email
     *
     * @param string $email
     * @param string $purpose
     * @param int $codeLength
     * @param int $expiryMinutes
     * @return array
     */
    function send_verification_code(
        string $email,
        string $purpose = 'verification',
        int $codeLength = 6,
        int $expiryMinutes = 15
    ): array {
        return \App\Helpers\EmailVerificationHelper::sendVerificationCode(
            $email,
            $purpose,
            $codeLength,
            $expiryMinutes
        );
    }
}

if (!function_exists('verify_code')) {
    /**
     * Verify email verification code
     *
     * @param string $email
     * @param string $code
     * @param string $purpose
     * @param int $maxAttempts
     * @return array
     */
    function verify_code(
        string $email,
        string $code,
        string $purpose = 'verification',
        int $maxAttempts = 3
    ): array {
        return \App\Helpers\EmailVerificationHelper::verifyCode(
            $email,
            $code,
            $purpose,
            $maxAttempts
        );
    }
}
