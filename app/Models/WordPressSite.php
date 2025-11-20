<?php

namespace App\Models;

use App\Enums\WordPressSiteStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class WordPressSite extends Model
{
    use HasFactory;

    protected $table = 'wordpress_sites';

    protected $fillable = [
        'name',
        'domain',
        'container_name',
        'server_host',
        'server_port',
        'server_user',
        'auth_type',
        'server_password',
        'server_private_key',
        'docker_image',
        'database_name',
        'database_username',
        'database_password',
        'status',
        'environment',
        'meta',
        'deployed_at',
        'last_health_check_at',
    ];

    protected $casts = [
        'environment' => 'array',
        'meta' => 'array',
        'status' => WordPressSiteStatus::class,
        'server_password' => 'encrypted',
        'server_private_key' => 'encrypted',
        'database_password' => 'encrypted',
        'deployed_at' => 'datetime',
        'last_health_check_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $site): void {
            if (! $site->container_name) {
                $site->container_name = Str::slug($site->domain ?: $site->name);
            }

            $site->status ??= WordPressSiteStatus::Deploying;
        });
    }

    public function markStatus(WordPressSiteStatus $status, ?array $meta = null): void
    {
        $this->forceFill([
            'status' => $status,
            'last_health_check_at' => now(),
            'meta' => array_merge($this->meta ?? [], $meta ?? []),
        ])->save();
    }

    public function credentials(): array
    {
        return [
            'host' => $this->server_host,
            'port' => $this->server_port,
            'user' => $this->server_user,
            'auth_type' => $this->auth_type,
            'password' => $this->server_password,
            'private_key' => $this->server_private_key,
        ];
    }

    public function dockerEnvironment(): array
    {
        return array_merge([
            'WORDPRESS_DB_NAME' => $this->database_name,
            'WORDPRESS_DB_USER' => $this->database_username,
            'WORDPRESS_DB_PASSWORD' => $this->database_password,
            'WORDPRESS_CONFIG_EXTRA' => 'define("WP_HOME","https://'.$this->domain.'"); define("WP_SITEURL","https://'.$this->domain.'");',
        ], $this->environment ?? []);
    }
}
