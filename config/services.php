<?php

return [

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

    // Used by App\Services\PersuasionPracticeAiService for the "Practice"
    // buyer-roleplay feature (buyer chat replies + end-of-session scoring).
    // 'ai_provider' picks which backend is actually called — lets you test
    // for free on Gemini's free tier now, and switch back to 'anthropic'
    // later by changing one env value, no code changes needed.
    'ai_provider' => env('AI_PROVIDER', 'anthropic'), // 'anthropic' | 'gemini'

    'anthropic' => [
        'key'   => env('ANTHROPIC_API_KEY'),
        'model' => env('ANTHROPIC_MODEL', 'claude-sonnet-4-6'),
    ],

    'gemini' => [
        'key'   => env('GEMINI_API_KEY'),
        // Flash-Lite has the most generous free-tier request limits as of
        // mid-2026 — good fit for testing without billing enabled.
        'model' => env('GEMINI_MODEL', 'gemini-2.5-flash-lite'),
    ],

];