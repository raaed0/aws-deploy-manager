<?php

namespace App\Jobs;

use App\Enums\WordPressSiteStatus;
use App\Models\WordPressSite;
use App\Services\WordPress\WordPressDeploymentService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class DeployWordPressSite implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public WordPressSite $site)
    {
        $this->onQueue('sites');
    }

    public function handle(WordPressDeploymentService $deployment): void
    {
        $site = $this->site->fresh() ?? $this->site;

        try {
            $site->markStatus(WordPressSiteStatus::Deploying);
            $deployment->deploy($site);
        } catch (Throwable $exception) {
            $site->markStatus(WordPressSiteStatus::Failed, [
                'error' => $exception->getMessage(),
            ]);

            Log::error('WordPress deployment failed', [
                'site_id' => $site->id,
                'error' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }
}
