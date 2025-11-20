<?php

namespace App\Services\WordPress;

use App\Models\WordPressSite;

class DockerComposeBuilder
{
    public function rootPath(WordPressSite $site): string
    {
        $root = rtrim(config('wordpress.compose_path'), '/');

        return "{$root}/{$site->container_name}";
    }

    public function composeFile(WordPressSite $site): string
    {
        $network = config('wordpress.network');
        $image = $site->docker_image ?: config('wordpress.docker_image');
        $hostPort = $this->hostPort($site);

        return <<<YAML
version: '3.9'

services:
  {$site->container_name}:
    image: {$image}
    container_name: {$site->container_name}
    restart: unless-stopped
    depends_on:
      - {$site->container_name}_db
    env_file:
      - .env
    environment:
      WORDPRESS_DB_HOST: {$site->container_name}_db:3306
    volumes:
      - {$site->container_name}_html:/var/www/html
    networks:
      - {$network}
    ports:
      - "{$hostPort}:80"

  {$site->container_name}_db:
    image: mariadb:11
    restart: unless-stopped
    environment:
      MYSQL_DATABASE: {$site->database_name}
      MYSQL_USER: {$site->database_username}
      MYSQL_PASSWORD: {$site->database_password}
      MYSQL_RANDOM_ROOT_PASSWORD: '1'
    volumes:
      - {$site->container_name}_db:/var/lib/mysql
    networks:
      - {$network}

networks:
  {$network}:
    external: true

volumes:
  {$site->container_name}_db:
  {$site->container_name}_html:
YAML;
    }

    public function environmentFile(WordPressSite $site): string
    {
        $defaults = config('wordpress.default_environment', []);
        $variables = array_merge($defaults, $site->dockerEnvironment());

        return collect($variables)
            ->map(fn ($value, $key) => "{$key}={$value}")
            ->implode(PHP_EOL);
    }

    protected function hostPort(WordPressSite $site): int
    {
        return 8000 + (abs(crc32($site->container_name)) % 1000);
    }
}
