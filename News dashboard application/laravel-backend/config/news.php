<?php

return [
    'api_key' => env('NEWS_API_KEY'),
    'auto_fetch_enabled' => env('AUTO_FETCH_ENABLED', true),
    'auto_fetch_category' => env('AUTO_FETCH_CATEGORY', 'technology'),
    'auto_fetch_count' => env('AUTO_FETCH_COUNT', 10),
];
