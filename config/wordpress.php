<?php

return [
    'docker_image' => env('WORDPRESS_DOCKER_IMAGE', 'wordpress:latest'),
    'network' => env('WORDPRESS_DOCKER_NETWORK', 'wp_sites'),
    'compose_path' => env('WORDPRESS_DOCKER_PATH', '/opt/wp-sites'),
    'monitoring_token' => env('MONITORING_ACCESS_TOKEN'),
    'monitoring_url' => env('MONITORING_ENDPOINT'),
    'remote' => [
        'host' => env('WORDPRESS_REMOTE_HOST'),
        'port' => (int) env('WORDPRESS_REMOTE_PORT', 22),
        'user' => env('WORDPRESS_REMOTE_USER', 'ec2-user'),
        'private_key' => env('WORDPRESS_REMOTE_PRIVATE_KEY'),
        'private_key_path' => env('WORDPRESS_REMOTE_PRIVATE_KEY_PATH'),
    ],
    'host_port' => env('WORDPRESS_HOST_PORT'), // optional static host port (e.g., 80) for single-site hosts
    'default_environment' => [
        'WORDPRESS_DB_HOST' => env('WORDPRESS_DB_HOST', 'database'),
        'AUTOMATIC_UPDATER_DISABLED' => 'true',
        'WP_ENVIRONMENT_TYPE' => env('WP_ENVIRONMENT_TYPE', 'production'),
    ],
];
