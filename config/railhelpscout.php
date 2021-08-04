<?php

return [
    // database
    'database_connection_name' => 'musora_mysql',
    'data_mode' => 'client', // 'host' or 'client', hosts do the db migrations, clients do not

    'helpscout_credentials' => [
        'app_id' => env('HELPSCOUT_APP_ID'),
        'app_secret' => env('HELPSCOUT_APP_SECRET'),
    ],
];
