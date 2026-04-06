<?php

/**
 * Additional User Language Lines for Improved Authentication
 * Add these to your existing resources/lang/en/user.php file
 */

return [

    // Validation & Errors
    'validation_error' => 'Please check the following errors',
    'server_error_occurred' => 'An error occurred. Please try again later.',
    'invalid_request' => 'Invalid request. Please try again.',

    // Password Requirements
    'password_requirements' => 'Password must contain at least one uppercase letter, one lowercase letter, and one number',
    'password_same_as_old' => 'New password cannot be the same as your old password',

    // Rate Limiting
    'too_many_login_attempts' => 'Too many login attempts. Please try again in :minutes minutes.',
    'too_many_otp_attempts' => 'Too many OTP verification attempts. Please try again in :minutes minutes.',

    // Account Status
    'account_suspended' => 'Your account has been suspended. Please contact support.',
    'email_not_verified' => 'Please verify your email address before logging in.',

    // Invitation Code
    'invalid_invitation_code' => 'The invitation code is invalid or has expired.',

    // Success Messages
    'register_success' => 'Registration successful! Welcome aboard.',
    'login_success' => 'Login successful! Welcome back.',
    'password_reset_success' => 'Password has been reset successfully. Please login with your new password.',
    'otp_verified' => 'OTP verified successfully.',

    // Failure Messages
    'registration_failed' => 'Registration failed. Please try again.',
    'login_failed' => 'Login failed. Please try again.',
    'password_reset_failed' => 'Password reset failed. Please try again.',
    'otp_verification_failed' => 'OTP verification failed. Please try again.',

];
