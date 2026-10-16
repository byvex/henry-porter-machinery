<?php

return [
    'temporary_file_upload' => [
        'middleware' => ['web', 'auth', 'verified', 'can:manage-catalogue', 'throttle:60,1'],
        'rules' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp,gif,avif', 'max:10240'],
    ],
];
