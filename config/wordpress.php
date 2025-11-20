<?php

return [
    'docker_image' => env('WORDPRESS_DOCKER_IMAGE', 'wordpress:latest'),
    'network' => env('WORDPRESS_DOCKER_NETWORK', 'wp_sites'),
    'compose_path' => env('WORDPRESS_DOCKER_PATH', '/opt/wp-sites'),
    'monitoring_token' => env('MONITORING_ACCESS_TOKEN'),
    'monitoring_url' => env('MONITORING_ENDPOINT'),
    'default_environment' => [
        'WORDPRESS_DB_HOST' => env('WORDPRESS_DB_HOST', 'database'),
        'AUTOMATIC_UPDATER_DISABLED' => 'true',
        'WP_ENVIRONMENT_TYPE' => env('WP_ENVIRONMENT_TYPE', 'production'),
    ],
];
