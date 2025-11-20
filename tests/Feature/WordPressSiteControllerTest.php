<?php

namespace Tests\Feature;

use App\Jobs\DeployWordPressSite;
use App\Models\WordPressSite;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class WordPressSiteControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_wordpress_site_and_dispatches_provision_job(): void
    {
        Queue::fake();

        $site = WordPressSite::factory()->make();

        $payload = [
            'name' => $site->name,
            'domain' => $site->domain,
            'container_name' => $site->container_name,
            'server_host' => $site->server_host,
            'server_port' => $site->server_port,
            'server_user' => $site->server_user,
            'auth_type' => 'key',
            'server_private_key' => $site->server_private_key,
            'docker_image' => $site->docker_image,
            'database_name' => $site->database_name,
            'database_username' => $site->database_username,
            'database_password' => $site->database_password,
            'environment' => [
                ['key' => 'WP_ENVIRONMENT_TYPE', 'value' => 'staging'],
            ],
        ];

        $response = $this->post(route('sites.store'), $payload);

        $response->assertRedirect(route('sites.index'));
        $this->assertDatabaseHas('wordpress_sites', [
            'domain' => $site->domain,
            'server_host' => $site->server_host,
        ]);

        Queue::assertPushed(DeployWordPressSite::class);
    }

    public function test_validation_errors_are_returned_when_required_fields_missing(): void
    {
        Queue::fake();

        $response = $this->post(route('sites.store'), [
            'name' => 'Test',
        ]);

        $response->assertSessionHasErrors([
            'domain',
            'server_host',
            'docker_image',
            'database_name',
        ]);
    }
}
