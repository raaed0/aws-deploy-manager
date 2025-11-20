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
use Throwable;

class StopWordPressSite implements ShouldQueue
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
            $deployment->stop($site);
        } catch (Throwable $exception) {
            $site->markStatus(WordPressSiteStatus::Failed, [
                'error' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }
}
