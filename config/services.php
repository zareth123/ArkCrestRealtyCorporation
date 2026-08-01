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
            'bot_user_oauth_token' => env(
                'SLACK_BOT_USER_OAUTH_TOKEN'
            ),

            'channel' => env(
                'SLACK_BOT_USER_DEFAULT_CHANNEL'
            ),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | AI Provider
    |--------------------------------------------------------------------------
    |
    | Available providers:
    | - anthropic
    | - gemini
    | - openai
    |
    */

    'ai_provider' => env('AI_PROVIDER', 'openai'),

    /*
    |--------------------------------------------------------------------------
    | Anthropic
    |--------------------------------------------------------------------------
    */

    'anthropic' => [
        'key' => env('ANTHROPIC_API_KEY'),

        'model' => env(
            'ANTHROPIC_MODEL',
            'claude-sonnet-4-6'
        ),
    ],

    /*
    |--------------------------------------------------------------------------
    | Gemini
    |--------------------------------------------------------------------------
    */

    'gemini' => [
        'key' => env('GEMINI_API_KEY'),

        'model' => env(
            'GEMINI_MODEL',
            'gemini-2.5-flash-lite'
        ),
    ],

    /*
    |--------------------------------------------------------------------------
    | OpenAI
    |--------------------------------------------------------------------------
    */

    'openai' => [
        'key' => env('OPENAI_API_KEY'),

        'model' => env(
            'OPENAI_MODEL',
            'gpt-5.6-luna'
        ),

        'base_url' => env(
            'OPENAI_BASE_URL',
            'https://api.openai.com/v1'
        ),
    ],

];