<?php

namespace App\Services;

use App\Models\Otp;
use App\Models\User;
use App\Notifications\Auth\SendOTPEmailVerificationOTP;
use App\Notifications\Auth\SendOTPPasswordReset;
use Illuminate\Support\Facades\Log;

class OtpService
{
    const TYPE_EMAIL = 'email';
    const TYPE_PASSWORD = 'password';

    const EXPIRY_MINUTES = 10;

    /**
     * Generate and send OTP for the specified type
     */
    public function generateAndSendOtp(User $user, string $type): array
    {
        // Validate OTP type
        if (!in_array($type, [self::TYPE_EMAIL, self::TYPE_PASSWORD])) {
            throw new \InvalidArgumentException("Invalid OTP type: {$type}");
        }

        // Clean up existing OTPs of this type for the user
        $this->cleanupExistingOtp($user, $type);

        // Generate new OTP
        $otpCode = $this->generateOtpCode();

        // Store OTP in database
        $otp = $user->otps()->create([
            'type' => $type,
            'otp' => $otpCode,
            'expires_at' => now()->addMinutes(self::EXPIRY_MINUTES),
        ]);

        // Send notification
        $this->sendOtpNotification($user, $otpCode, $type);

        return [
            'success' => true,
            'message' => $this->getSuccessMessage($type, 'sent'),
            'expires_in_minutes' => self::EXPIRY_MINUTES
        ];
    }

    /**
     * Verify OTP for the specified user and type
     */
    public function verifyOtp(User $user, string $otpCode, string $type): array
    {
        // Find valid OTP
        $otpRecord = $user->otps()
            ->where('type', $type)
            ->where('otp', $otpCode)
            ->where('expires_at', '>', now())
            ->first();

        if (!$otpRecord) {
            return [
                'success' => false,
                'message' => __('Invalid or expired OTP.')
            ];
        }

        return [
            'success' => true,
            'message' => $this->getSuccessMessage($type, 'verified'),
            'otp_record' => $otpRecord
        ];
    }

    /**
     * Resend OTP for the specified user and type
     */
    public function resendOtp(User $user, string $type): array
    {
        // Clean up existing OTP
        $this->cleanupExistingOtp($user, $type);

        // Generate and send new OTP
        return $this->generateAndSendOtp($user, $type);
    }

    /**
     * Check if user has valid OTP of specified type
     */
    public function hasValidOtp(User $user, string $type): bool
    {
        return $user->otps()
            ->where('type', $type)
            ->where('expires_at', '>', now())
            ->exists();
    }

    /**
     * Clean up expired OTPs for all users (can be called from scheduled job)
     */
    public function cleanupExpiredOtps(): int
    {
        $deletedCount = Otp::where('expires_at', '<=', now())->delete();

        Log::info("Cleaned up expired OTPs", ['count' => $deletedCount]);

        return $deletedCount;
    }

    /**
     * Clean up existing OTPs for user and type
     */
    private function cleanupExistingOtp(User $user, string $type): void
    {
        $user->otps()->where('type', $type)->delete();
    }

    /**
     * Generate a random 6-digit OTP code
     */
    private function generateOtpCode(): string
    {
        return (string) random_int(100000, 999999);
    }

    /**
     * Send OTP notification based on type
     */
    private function sendOtpNotification(User $user, string $otpCode, string $type): void
    {
        switch ($type) {
            case self::TYPE_EMAIL:
                $user->notifyNow(new SendOTPEmailVerificationOTP($otpCode));
                break;
            case self::TYPE_PASSWORD:
                $user->notifyNow(new SendOTPPasswordReset($otpCode));
                break;
            default:
                throw new \InvalidArgumentException("No notification configured for OTP type: {$type}");
        }
    }

    /**
     * Get success message based on OTP type and action
     */
    private function getSuccessMessage(string $type, string $action): string
    {
        $messages = [
            self::TYPE_EMAIL => [
                'sent' => __('Email verification code sent to your email.'),
                'verified' => __('Email verified successfully!'),
            ],
            self::TYPE_PASSWORD => [
                'sent' => __('Password reset code sent to your email.'),
                'verified' => __('OTP verified successfully. You can now reset your password.'),
            ]
        ];

        return $messages[$type][$action] ?? __('OTP action completed successfully.');
    }

    /**
     * Complete email verification process
     */
    public function completeEmailVerification(User $user): array
    {
        if ($user->hasVerifiedEmail()) {
            return [
                'success' => false,
                'message' => __('Email is already verified.')
            ];
        }

        // Mark email as verified
        $user->update([
            'email_verified_at' => now()
        ]);

        // Clean up email OTPs
        $this->cleanupExistingOtp($user, self::TYPE_EMAIL);

        return [
            'success' => true,
            'message' => __('Email verified successfully! Welcome aboard!')
        ];
    }
}