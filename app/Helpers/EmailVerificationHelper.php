<?php

// app/Helpers/EmailVerificationHelper.php
namespace App\Helpers;

use App\Mail\VerificationCodeMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

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
            // Generate verification code
            $code = self::generateCode($codeLength);
            
            // Create cache key
            $cacheKey = self::getCacheKey($email, $purpose);
            
            // Store code in cache with expiry
            Cache::put($cacheKey, [
                'code' => $code,
                'email' => $email,
                'purpose' => $purpose,
                'created_at' => now(),
                'attempts' => 0
            ], now()->addMinutes($expiryMinutes));
            
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
        $cacheKey = self::getCacheKey($email, $purpose);
        $storedData = Cache::get($cacheKey);
        
        if (!$storedData) {
            return [
                'success' => false,
                'message' => 'Verification code expired or not found'
            ];
        }
        
        // Check attempts
        if ($storedData['attempts'] >= $maxAttempts) {
            Cache::forget($cacheKey);
            return [
                'success' => false,
                'message' => 'Maximum verification attempts exceeded'
            ];
        }
        
        // Increment attempts
        $storedData['attempts']++;
        Cache::put($cacheKey, $storedData, now()->addMinutes(15));
        
        // Verify code
        if ($storedData['code'] !== $code) {
            return [
                'success' => false,
                'message' => 'Invalid verification code',
                'attempts_remaining' => $maxAttempts - $storedData['attempts']
            ];
        }
        
        // Code is valid, remove from cache
        Cache::forget($cacheKey);
        
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
        // Generate numeric code
        $code = '';
        for ($i = 0; $i < $length; $i++) {
            $code .= random_int(0, 9);
        }
        return $code;
    }
    
    /**
     * Get cache key for verification code
     *
     * @param string $email
     * @param string $purpose
     * @return string
     */
    private static function getCacheKey(string $email, string $purpose): string
    {
        return "verification_code:{$purpose}:" . md5($email);
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
        $cacheKey = self::getCacheKey($email, $purpose);
        return Cache::has($cacheKey);
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
        $cacheKey = self::getCacheKey($email, $purpose);
        $storedData = Cache::get($cacheKey);
        
        if (!$storedData) {
            return null;
        }
        
        $expiresAt = $storedData['created_at']->addMinutes(15);
        return max(0, $expiresAt->diffInSeconds(now()));
    }
}

// app/Mail/VerificationCodeMail.php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VerificationCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $verificationCode;
    public string $purpose;
    public int $expiryMinutes;

    public function __construct(string $verificationCode, string $purpose, int $expiryMinutes)
    {
        $this->verificationCode = $verificationCode;
        $this->purpose = $purpose;
        $this->expiryMinutes = $expiryMinutes;
    }

    public function envelope(): Envelope
    {
        $subject = match($this->purpose) {
            'registration' => 'Complete Your Registration',
            'password_reset' => 'Reset Your Password',
            'login' => 'Login Verification Code',
            default => 'Verification Code'
        };

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.verification-code',
            with: [
                'code' => $this->verificationCode,
                'purpose' => $this->purpose,
                'expiryMinutes' => $this->expiryMinutes
            ]
        );
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

// Usage Examples:

// 1. In a Controller
class AuthController extends Controller
{
    public function sendVerificationCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $result = send_verification_code(
            $request->email,
            'registration',
            6,
            15
        );

        return response()->json($result);
    }

    public function verifyCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|string|size:6'
        ]);

        $result = verify_code(
            $request->email,
            $request->code,
            'registration'
        );

        return response()->json($result);
    }
}

// 2. Direct usage
/*
// Send code
$result = send_verification_code('user@example.com', 'password_reset');

if ($result['success']) {
    // Code sent successfully
    echo "Verification code sent!";
} else {
    // Handle error
    echo $result['message'];
}

// Verify code
$result = verify_code('user@example.com', '123456', 'password_reset');

if ($result['success']) {
    // Code is valid
    echo "Code verified successfully!";
} else {
    // Invalid code
    echo $result['message'];
}
*/