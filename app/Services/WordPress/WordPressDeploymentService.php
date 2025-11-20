<?php

namespace App\Services\WordPress;

use App\Enums\WordPressSiteStatus;
use App\Models\WordPressSite;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class WordPressDeploymentService
{
    public function __construct(
        protected DockerComposeBuilder $builder
    ) {
    }

    public function deploy(WordPressSite $site): void
    {
        $client = RemoteServerClient::fromSite($site);
        $path = $this->builder->rootPath($site);

        $client->ensureDirectory($path);
        $client->upload("{$path}/docker-compose.yml", $this->builder->composeFile($site));
        $client->upload("{$path}/.env", $this->builder->environmentFile($site));
        $this->ensureNetwork($client);

        $this->runDockerCommand($client, $path, 'docker compose pull');
        $this->runDockerCommand($client, $path, 'docker compose up -d');

        $site->markStatus(WordPressSiteStatus::Running);
        $site->forceFill(['deployed_at' => now()])->save();
    }

    public function start(WordPressSite $site): void
    {
        $this->runLifecycleCommand($site, 'docker compose up -d', WordPressSiteStatus::Running);
    }

    public function stop(WordPressSite $site): void
    {
        $this->runLifecycleCommand($site, 'docker compose stop', WordPressSiteStatus::Stopped);
    }

    public function destroy(WordPressSite $site): void
    {
        $client = RemoteServerClient::fromSite($site);
        $path = $this->builder->rootPath($site);

        $this->runDockerCommand($client, $path, 'docker compose down -v || true');
        $client->run(sprintf('rm -rf %s', escapeshellarg($path)));

        $site->delete();
    }

    protected function runLifecycleCommand(
        WordPressSite $site,
        string $command,
        WordPressSiteStatus $status
    ): void {
        $client = RemoteServerClient::fromSite($site);
        $path = $this->builder->rootPath($site);

        $this->runDockerCommand($client, $path, $command);
        $site->markStatus($status);
    }

    protected function runDockerCommand(RemoteServerClient $client, string $path, string $command): void
    {
        $fullCommand = sprintf('cd %s && %s', escapeshellarg($path), $command);

        try {
            $client->run($fullCommand);
        } catch (Throwable $exception) {
            Log::error('Failed to run remote docker command', [
                'path' => $path,
                'command' => $command,
                'message' => $exception->getMessage(),
            ]);

            throw new RuntimeException("Unable to run remote docker command: {$command}", previous: $exception);
        }
    }

    protected function ensureNetwork(RemoteServerClient $client): void
    {
        $network = config('wordpress.network');

        if (! $network) {
            return;
        }

        $command = sprintf(
            'docker network inspect %1$s >/dev/null 2>&1 || docker network create %1$s',
            escapeshellarg($network)
        );

        $client->run($command);
    }
}
