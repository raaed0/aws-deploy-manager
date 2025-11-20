<?php

namespace App\Http\Middleware;

use App\Enums\WordPressSiteStatus;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'app' => [
                'name' => config('app.name'),
                'statusOptions' => WordPressSiteStatus::options(),
            ],
            'flash' => [
                'banner' => fn () => $request->session()->get('flash.banner'),
                'error' => fn () => $request->session()->get('flash.error'),
            ],
        ]);
    }
}
