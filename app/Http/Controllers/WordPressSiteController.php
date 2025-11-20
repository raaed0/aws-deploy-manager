<?php

namespace App\Http\Controllers;

use App\Enums\WordPressSiteStatus;
use App\Jobs\DeployWordPressSite;
use App\Jobs\DestroyWordPressSite;
use App\Jobs\StartWordPressSite;
use App\Jobs\StopWordPressSite;
use App\Models\WordPressSite;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class WordPressSiteController extends Controller
{
    public function index(Request $request): Response
    {
        $sites = WordPressSite::query()
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = (string) $request->input('search');

                $query->where(function ($inner) use ($search): void {
                    $inner
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('domain', 'like', "%{$search}%")
                        ->orWhere('server_host', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('status'), function ($query) use ($request): void {
                $query->where('status', (string) $request->input('status'));
            })
            ->latest()
            ->paginate(10)
            ->withQueryString()
            ->through(fn (WordPressSite $site) => [
                'id' => $site->id,
                'name' => $site->name,
                'domain' => $site->domain,
                'status' => $site->status->value,
                'status_label' => $site->status->label(),
                'server' => "{$site->server_user}@{$site->server_host}",
                'container_name' => $site->container_name,
                'meta' => $site->meta,
                'deployed_at' => optional($site->deployed_at)->toDateTimeString(),
            ]);

        $summary = [
            'total' => WordPressSite::count(),
            'running' => WordPressSite::where('status', WordPressSiteStatus::Running)->count(),
            'stopped' => WordPressSite::where('status', WordPressSiteStatus::Stopped)->count(),
            'failed' => WordPressSite::where('status', WordPressSiteStatus::Failed)->count(),
        ];

        return Inertia::render('Sites/Index', [
            'sites' => $sites,
            'filters' => $request->only(['search', 'status']),
            'statusOptions' => WordPressSiteStatus::options(),
            'summary' => $summary,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Sites/Create', [
            'default' => [
                'docker_image' => config('wordpress.docker_image'),
                'server_port' => 22,
                'auth_type' => 'key',
                'environment' => [],
            ],
            'statusOptions' => WordPressSiteStatus::options(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);

        $site = WordPressSite::create($data);
        DeployWordPressSite::dispatch($site);

        return redirect()->route('sites.index')
            ->with('flash.banner', "Provisioning {$site->domain} has started.");
    }

    public function edit(WordPressSite $site): Response
    {
        return Inertia::render('Sites/Edit', [
            'site' => $site->only([
                'id',
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
                'environment',
                'meta',
                'deployed_at',
                'last_health_check_at',
            ]) + [
                'status' => $site->status->value,
                'status_label' => $site->status->label(),
            ],
            'statusOptions' => WordPressSiteStatus::options(),
        ]);
    }

    public function update(Request $request, WordPressSite $site): RedirectResponse
    {
        $data = $this->validatedData($request, $site);
        $site->update($data);

        DeployWordPressSite::dispatch($site);

        return redirect()
            ->route('sites.index')
            ->with('flash.banner', "{$site->domain} is being updated.");
    }

    public function destroy(WordPressSite $site): RedirectResponse
    {
        DestroyWordPressSite::dispatchSync($site);

        return redirect()->route('sites.index')
            ->with('flash.banner', "{$site->domain} has been removed.");
    }

    public function start(WordPressSite $site): RedirectResponse
    {
        StartWordPressSite::dispatch($site);

        return back()->with('flash.banner', "Starting {$site->domain}...");
    }

    public function stop(WordPressSite $site): RedirectResponse
    {
        StopWordPressSite::dispatch($site);

        return back()->with('flash.banner', "Stopping {$site->domain}...");
    }

    protected function validatedData(Request $request, ?WordPressSite $site = null): array
    {
        $environment = collect($request->input('environment', []))
            ->filter(fn ($pair) => filled(data_get($pair, 'key')))
            ->mapWithKeys(fn ($pair) => [
                data_get($pair, 'key') => data_get($pair, 'value'),
            ])->all();

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'domain' => [
                'required',
                'string',
                'max:255',
                'regex:/^([a-z0-9]+(-[a-z0-9]+)*\.)+[a-z]{2,}$/i',
                Rule::unique('wordpress_sites', 'domain')->ignore($site),
            ],
            'container_name' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('wordpress_sites', 'container_name')->ignore($site),
            ],
            'server_host' => ['required', 'string', 'max:255'],
            'server_port' => ['required', 'integer', 'min:1', 'max:65535'],
            'server_user' => ['required', 'string', 'max:255'],
            'auth_type' => ['required', Rule::in(['password', 'key'])],
            'server_password' => ['nullable', 'string', Rule::requiredIf($request->input('auth_type') === 'password')],
            'server_private_key' => ['nullable', 'string', Rule::requiredIf($request->input('auth_type') === 'key')],
            'docker_image' => ['required', 'string', 'max:255'],
            'database_name' => ['required', 'string', 'max:191'],
            'database_username' => ['required', 'string', 'max:191'],
            'database_password' => ['required', 'string', 'max:191'],
            'environment' => ['array'],
        ];

        $data = $request->validate($rules);
        $data['environment'] = $environment;

        return $data;
    }
}
