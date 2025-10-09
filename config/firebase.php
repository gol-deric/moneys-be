<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Firebase Credentials
    |--------------------------------------------------------------------------
    |
    | Path to the Firebase service account credentials JSON file.
    | You can obtain this file from the Firebase console.
    |
    */

    'credentials' => env('FIREBASE_CREDENTIALS', storage_path('app/firebase-credentials.json')),
];
