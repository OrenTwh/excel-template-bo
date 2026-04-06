<?php

/**
 * Logging Configuration Example
 * Add this to config/logging.php in the 'channels' array
 */

return [
    'channels' => [

        // ... existing channels (stack, single, daily, etc.)

        /**
         * Authentication Logging Channel
         * Logs all authentication events (login, register, password reset, etc.)
         */
        'auth' => [
            'driver' => 'daily',
            'path' => storage_path('logs/auth.log'),
            'level' => env('AUTH_LOG_LEVEL', 'info'),
            'days' => 30, // Keep logs for 30 days
            'permission' => 0664,
        ],

        /**
         * Security Events Channel (Optional)
         * For tracking security-related events separately
         */
        'security' => [
            'driver' => 'daily',
            'path' => storage_path('logs/security.log'),
            'level' => env('SECURITY_LOG_LEVEL', 'warning'),
            'days' => 90, // Keep security logs for 90 days
            'permission' => 0664,
        ],

        /**
         * API Requests Channel (Optional)
         * Already exists in your codebase, but can be enhanced
         */
        'api' => [
            'driver' => 'daily',
            'path' => storage_path('logs/api.log'),
            'level' => env('API_LOG_LEVEL', 'info'),
            'days' => 14,
            'permission' => 0664,
        ],

        /**
         * Database Query Logging (Optional, for debugging)
         * Useful for development and debugging
         */
        'database' => [
            'driver' => 'daily',
            'path' => storage_path('logs/database.log'),
            'level' => env('DB_LOG_LEVEL', 'debug'),
            'days' => 7,
            'permission' => 0664,
        ],

    ],
];

/**
 * Environment Variables (.env)
 * Add these to your .env file for configuration
 */

/*

# Logging Configuration
LOG_CHANNEL=stack
LOG_LEVEL=debug

# Authentication Logging
AUTH_LOG_LEVEL=info

# Security Logging
SECURITY_LOG_LEVEL=warning

# API Logging
API_LOG_LEVEL=info

# Database Query Logging (only enable in development)
DB_LOG_LEVEL=debug
LOG_QUERY=false

# Rate Limiting Configuration
LOGIN_MAX_ATTEMPTS=5
LOGIN_DECAY_MINUTES=15
OTP_MAX_ATTEMPTS=10
OTP_DECAY_MINUTES=15

*/

/**
 * Usage in Code:
 *
 * Log to auth channel:
 * Log::channel('auth')->info('Login attempt', ['email' => $email]);
 *
 * Log to security channel:
 * Log::channel('security')->warning('Suspicious activity detected', ['ip' => $ip]);
 *
 * Log to multiple channels:
 * Log::stack(['auth', 'security'])->critical('Security breach attempt');
 */

/**
 * Monitoring Commands:
 *
 * View auth logs:
 * tail -f storage/logs/auth.log
 *
 * Search for failed logins:
 * grep "Invalid Credentials" storage/logs/auth.log
 *
 * Count failed login attempts:
 * grep -c "Invalid Credentials" storage/logs/auth.log
 *
 * Find all logs for a specific user:
 * grep "user@example.com" storage/logs/auth.log
 *
 * View last 100 lines:
 * tail -n 100 storage/logs/auth.log
 */
