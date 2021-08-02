<?php

return [

    /*
     * The view id of which you want to display data.
     */
    'view_id' => env('ANALYTICS_VIEW_ID'),

    /*
     * Path to the client secret json file. Take a look at the README of this package
     * to learn how to get this file. You can also pass the credentials as an array
     * instead of a file path.
     */
    'service_account_credentials_json' => ROOT . DS . 'config' . DS . 'service-account-credentials.json',

    /*
     * The amount of minutes the Google API responses will be cached.
     * If you set this to zero, the responses won't be cached at all.
     */
    'cache_lifetime_in_minutes' => 60 * 24,

    /*
     * The names of the cache engines to use
     */
    'cache' => [
        'analytics' => 'google_analytics', // Required for caching google analytics results
        'auth' => false, // The cache engine that the underlying Google_Client will use to store it's auth data
    ],
];
