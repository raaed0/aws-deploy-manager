<?php

namespace App\Jobs;

use App\Models\WordPressSite;
use App\Services\WordPress\WordPressDeploymentService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class DestroyWordPressSite implements ShouldQueue
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
        try {
            $deployment->destroy($this->site->fresh() ?? $this->site);
        } catch (Throwable $exception) {
            Log::error('Failed to destroy WordPress stack', [
                'site_id' => $this->site->id,
                'message' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }
}
