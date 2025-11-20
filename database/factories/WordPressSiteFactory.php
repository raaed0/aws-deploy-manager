<?php

namespace Database\Factories;

use App\Enums\WordPressSiteStatus;
use App\Models\WordPressSite;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<WordPressSite>
 */
class WordPressSiteFactory extends Factory
{
    protected $model = WordPressSite::class;

    public function definition(): array
    {
        $domain = $this->faker->domainName();
        $dbName = Str::replace('.', '_', $domain);

        return [
            'name' => Str::headline($domain),
            'domain' => $domain,
            'container_name' => Str::slug($domain),
            'availability_zone' => $this->faker->randomElement(['us-east-1a', 'us-east-1b', 'us-east-1c']),
            'server_host' => $this->faker->ipv4(),
            'server_port' => 22,
            'server_user' => 'root',
            'auth_type' => 'key',
            'server_private_key' => "-----BEGIN OPENSSH PRIVATE KEY-----\nfake-key\n-----END OPENSSH PRIVATE KEY-----",
            'docker_image' => 'wordpress:latest',
            'database_name' => $dbName,
            'database_username' => 'wp_'.$this->faker->userName(),
            'database_password' => $this->faker->password(16),
            'status' => $this->faker->randomElement(WordPressSiteStatus::cases())->value,
            'environment' => [
                'WP_ENVIRONMENT_TYPE' => 'staging',
            ],
            'meta' => [
                'php_version' => '8.2',
            ],
            'deployed_at' => now()->subDays(rand(0, 30)),
            'last_health_check_at' => now()->subMinutes(rand(0, 60)),
        ];
    }
}
