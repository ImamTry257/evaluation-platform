<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Location / Wilayah Configuration
    |--------------------------------------------------------------------------
    |
    | default_city_code : kode provinsi default aplikasi (regional).
    | all_province      : true = semua provinsi boleh diakses,
    |                     false = hanya default_city_code yang diizinkan.
    |
    */

    'default_city_code' => env('DEFAULT_CITY_CODE', '34'),

    'all_province' => env('APP_ALL_PROVINCE', false),
];
