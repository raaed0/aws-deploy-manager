<?php

namespace Tests\Feature;

use App\Enums\WordPressSiteStatus;
use App\Models\WordPressSite;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteStatusApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_updates_site_status_from_monitor(): void
    {
        config(['wordpress.monitoring_token' => 'example-token']);

        $site = WordPressSite::factory()->create([
            'status' => WordPressSiteStatus::Deploying,
        ]);

        $response = $this->postJson(
            route('api.site-status'),
            [
                'container' => $site->container_name,
                'status' => WordPressSiteStatus::Running->value,
                'uptime' => '2023-01-01T00:00:00Z',
            ],
            [
                'X-Monitor-Token' => 'example-token',
            ]
        );

        $response->assertOk()->assertJson([
            'ok' => true,
            'status' => WordPressSiteStatus::Running->value,
        ]);

        $this->assertEquals(WordPressSiteStatus::Running, $site->fresh()->status);
    }

    public function test_it_blocks_invalid_tokens(): void
    {
        config(['wordpress.monitoring_token' => 'example-token']);

        $site = WordPressSite::factory()->create();

        $response = $this->postJson(route('api.site-status'), [
            'container' => $site->container_name,
            'status' => 'running',
        ]);

        $response->assertForbidden();
    }
}
