<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'google_calendar' => [
        'client_id' => env('GOOGLE_CALENDAR_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CALENDAR_CLIENT_SECRET'),
        'refresh_token' => env('GOOGLE_CALENDAR_REFRESH_TOKEN'),
        'calendar_id' => env('GOOGLE_CALENDAR_ID'),
        'timezone' => env('APPOINTMENT_TIMEZONE'),
        'weekdays' => env('APPOINTMENT_WEEKDAYS', '1,2,3,4,5'),
        'open_time' => env('APPOINTMENT_OPEN_TIME', '09:00'),
        'close_time' => env('APPOINTMENT_CLOSE_TIME', '17:00'),
        'slot_interval' => (int) env('APPOINTMENT_SLOT_INTERVAL', 15),
        'booking_window_days' => (int) env('APPOINTMENT_BOOKING_WINDOW_DAYS', 60),
        'durations' => [
            'appointment' => (int) env('APPOINTMENT_DURATION_APPOINTMENT', 30),
            'services' => (int) env('APPOINTMENT_DURATION_SERVICES', 15),
            'location' => (int) env('APPOINTMENT_DURATION_LOCATION', 15),
            'callback' => (int) env('APPOINTMENT_DURATION_CALLBACK', 15),
            'other' => (int) env('APPOINTMENT_DURATION_OTHER', 30),
        ],
    ],

];
