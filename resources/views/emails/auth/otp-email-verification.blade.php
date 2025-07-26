<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Email Verification Code') }}</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f3f4f6; font-family: Arial, sans-serif; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale;">
<table width="100%" cellpadding="0" cellspacing="0" border="0">
    <tr>
        <td align="center" style="padding: 40px 0;">
            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);">
                <tr>
                    <td style="padding: 40px;">

                        <!-- Header -->
                        <div style="text-align: center; margin-bottom: 24px;">
                            <h1 style="margin: 0; font-size: 24px; color: #222E50; font-weight: bold; margin-bottom: 8px;">{{ __('Email Verification Request') }}</h1>
                            <p style="margin: 0; color: #6B7280; font-size: 14px;">{{ __('Hello') }} {{ $user->name }},</p>
                        </div>

                        <!-- Main Content -->
                        <div style="text-align: center; margin-bottom: 32px;">
                            <p style="margin: 0 0 24px 0; color: #6B7280; font-size: 16px; line-height: 24px;">
                                {{ __('We received a request to verify your account email. Use the verification code below to proceed:') }}
                            </p>

                            <!-- OTP Code Display -->
                            <div style="margin: 32px 0; padding: 24px; background-color: #f8fafc; border: 2px dashed #FCCB06; border-radius: 8px;">
                                <p style="margin: 0 0 8px 0; color: #222E50; font-size: 14px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px;">
                                    {{ __('Verification Code') }}
                                </p>
                                <div style="font-size: 32px; font-weight: bold; color: #222E50; letter-spacing: 4px; font-family: 'Courier New', monospace;">
                                    {{ $otp }}
                                </div>
                            </div>

                            <!-- Instructions -->
                            <div style="margin-bottom: 24px;">
                                <p style="margin: 0 0 16px 0; color: #6B7280; font-size: 14px; line-height: 20px;">
                                    {{ __('Enter this code on the email verification page to confirm your email address.') }}
                                </p>
                                <p style="margin: 0; color: #ef4444; font-size: 14px; font-weight: 600;">
                                    ⚠️ {{ __('This code expires in 10 minutes') }}
                                </p>
                            </div>

                            <!-- Security Notice -->
                            <div style="background-color: #fef3cd; border: 1px solid #fbbf24; border-radius: 6px; padding: 16px; margin-bottom: 24px;">
                                <p style="margin: 0; color: #92400e; font-size: 14px; line-height: 20px;">
                                    <strong>{{ __('Security Notice:') }}</strong><br>
                                    {{ __('If you didn\'t request this email verification, please ignore this email. Your account remains secure.') }}
                                </p>
                            </div>

                            <!-- Footer Text -->
                            <p style="margin: 0; color: #6B7280; font-size: 14px;">
                                {{ __('Having trouble? Contact our support team for assistance.') }}
                            </p>
                        </div>

                        <!-- Support Section -->
                        <div style="text-align: center; border-top: 1px solid #E5E7EB; margin-top: 24px; padding-top: 24px;">
                            <p style="margin: 0; color: #6B7280; font-size: 14px;">
                                {{ __('Need help? Contact') }}
                                <a href="mailto:support@dancycodes.com"
                                   style="color: #222E50; text-decoration: none; border-bottom: 1px solid #222E50;">
                                    {{ __('DancyCodes Support') }}
                                </a>
                            </p>
                            <p style="margin: 8px 0 0 0; color: #9CA3AF; font-size: 12px;">
                                {{ __('© :year DancyCodes. All rights reserved.', ['year' => date('Y')]) }}
                            </p>
                        </div>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>