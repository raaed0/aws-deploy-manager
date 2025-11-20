<?php

namespace App\Http\Controllers\Api;

use App\Enums\WordPressSiteStatus;
use App\Http\Controllers\Controller;
use App\Models\WordPressSite;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SiteStatusController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $this->guardToken($request);

        $data = $request->validate([
            'container' => ['required', 'string'],
            'status' => ['required', 'string'],
            'uptime' => ['nullable', 'string'],
            'message' => ['nullable', 'string'],
        ]);

        $site = WordPressSite::query()
            ->where('container_name', $data['container'])
            ->orWhere('domain', $data['container'])
            ->firstOrFail();

        $status = WordPressSiteStatus::tryFrom($data['status']) ?? WordPressSiteStatus::Failed;

        $site->markStatus($status, array_filter([
            'uptime' => $data['uptime'] ?? null,
            'message' => $data['message'] ?? null,
        ]));

        return response()->json([
            'ok' => true,
            'site_id' => $site->id,
            'status' => $site->status->value,
        ]);
    }

    protected function guardToken(Request $request): void
    {
        $token = config('wordpress.monitoring_token');

        if (! $token) {
            abort(Response::HTTP_FORBIDDEN, 'Monitoring token missing.');
        }

        $incoming = $request->header('X-Monitor-Token') ?? $request->input('token');

        if (! hash_equals($token, (string) $incoming)) {
            abort(Response::HTTP_FORBIDDEN, 'Invalid monitoring token.');
        }
    }
}
