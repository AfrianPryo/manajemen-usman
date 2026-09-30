<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
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

    /*
    |--------------------------------------------------------------------------
    | Fonnte (WhatsApp)
    |--------------------------------------------------------------------------
    |
    | Hanya dipakai oleh Database\Seeders\FonnteSettingsSeeder untuk mengisi
    | pengaturan Fonnte AWAL ke tabel `settings`. Setelah itu sumber
    | kebenarannya tetap tabel `settings` (Master > Pengaturan), bukan
    | file ini.
    |
    */

    'fonnte' => [
        'token'  => env('FONNTE_TOKEN'),
        'sender' => env('FONNTE_SENDER'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

];