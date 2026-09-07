<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Firebase Web (client) configuration
    |--------------------------------------------------------------------------
    |
    | Public config from Firebase console → Project settings → Your apps → Web.
    | Used by the JS SDK for social login and FCM. These values are not secret.
    |
    */

    'web' => [
        'apiKey' => env('FIREBASE_WEB_API_KEY'),
        'authDomain' => env('FIREBASE_WEB_AUTH_DOMAIN'),
        'projectId' => env('FIREBASE_WEB_PROJECT_ID'),
        'storageBucket' => env('FIREBASE_WEB_STORAGE_BUCKET'),
        'messagingSenderId' => env('FIREBASE_WEB_MESSAGING_SENDER_ID'),
        'appId' => env('FIREBASE_WEB_APP_ID'),
    ],

    // Web Push certificate key pair (Project settings → Cloud Messaging).
    'vapid_key' => env('FIREBASE_WEB_VAPID_KEY'),

    // Sign-in providers shown on the login page. Enable them in Firebase console too.
    'social_providers' => array_filter(explode(',', (string) env('LARAFIRE_SOCIAL_PROVIDERS', 'google,github'))),

    // Seconds to cache a Firebase user record between requests.
    'user_cache_ttl' => (int) env('LARAFIRE_USER_CACHE_TTL', 60),

    /*
    |--------------------------------------------------------------------------
    | Cloud Messaging
    |--------------------------------------------------------------------------
    */

    'fcm' => [
        'broadcast_topic' => env('LARAFIRE_FCM_TOPIC', 'larafire-all'),
        'user_topic_prefix' => 'larafire-user-',
    ],

    /*
    |--------------------------------------------------------------------------
    | Cloud Firestore
    |--------------------------------------------------------------------------
    */

    'firestore' => [
        'notes_collection' => env('LARAFIRE_NOTES_COLLECTION', 'notes'),
    ],

    'youtube_url' => 'https://www.youtube.com/@sevenstac',

    'repository_url' => 'https://github.com/suhasrkms/Lara-Fire',

];
