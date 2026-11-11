<?php

return [
    'temporary_file_upload' => [
        'middleware' => ['web', 'auth', 'verified', 'can:manage-catalogue', 'throttle:60,1'],
        'rules' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,gif,avif,csv,txt,xlsx,xls', 'max:20480'],
    ],
];
