<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Upload Disk
    |--------------------------------------------------------------------------
    |
    | The filesystem disk backing every user upload: activity media, support
    | documents, avatars and branding assets. Stored paths are disk-relative
    | keys such as "uploads/activities/images/foo.jpg", so pointing this at
    | "s3" serves uploads from a bucket with no database migration.
    |
    */

    'disk' => env('UPLOADS_DISK', 'public'),

    /*
    |--------------------------------------------------------------------------
    | Upload Directories
    |--------------------------------------------------------------------------
    |
    | Every upload location in the portal, relative to the disk root.
    |
    */

    'paths' => [
        'images'   => 'uploads/activities/images',
        'docs'     => 'uploads/activities/docs',
        'tmp'      => 'uploads/activities/tmp',
        'avatars'  => 'uploads/avatars',
        'settings' => 'uploads/settings',
        'portal'   => 'uploads/portal',
    ],

];
