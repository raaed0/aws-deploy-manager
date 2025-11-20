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
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
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
                        ->orWhere('domain', 'like', "%{$search}%");
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
                'container_name' => $site->container_name,
                'availability_zone' => $site->availability_zone,
                'docker_image' => $site->docker_image,
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
                'availability_zone' => null,
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
                'availability_zone',
                'docker_image',
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
        $this->assertRemoteDefaults();

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
            'availability_zone' => ['required', 'string', 'max:50'],
            'docker_image' => ['required', 'string', 'max:255'],
        ];

        $data = $request->validate($rules);
        $data['availability_zone'] = $data['availability_zone'] ?: 'us-east-1';
        $data['container_name'] = $data['container_name'] ?: Str::slug($data['domain']);

        // Server defaults (hidden from UI)
        $data['server_host'] = config('wordpress.remote.host');
        $data['server_port'] = config('wordpress.remote.port', 22);
        $data['server_user'] = config('wordpress.remote.user', 'ec2-user');
        $data['auth_type'] = 'key';
        $data['server_password'] = null;
        $data['server_private_key'] = $this->resolveRemoteKey();

        // Database credentials: reuse on update, generate on create
        $data['database_name'] = $site?->database_name ?? 'wp_' . Str::snake(Str::limit($data['container_name'], 20, ''));
        $data['database_username'] = $site?->database_username ?? 'wp_' . Str::random(8);
        $data['database_password'] = $site?->database_password ?? Str::random(24);

        // No user-provided environment overrides in this flow
        $data['environment'] = [];

        return $data;
    }

    protected function assertRemoteDefaults(): void
    {
        $host = config('wordpress.remote.host');
        $key = config('wordpress.remote.private_key') ?? config('wordpress.remote.private_key_path');

        if (! $host || ! $key) {
            throw ValidationException::withMessages([
                'server_host' => 'Remote host or private key is not configured. Set WORDPRESS_REMOTE_HOST and WORDPRESS_REMOTE_PRIVATE_KEY in .env.',
            ]);
        }
    }

    protected function resolveRemoteKey(): string
    {
        $inline = config('wordpress.remote.private_key');
        if ($inline) {
            return $inline;
        }

        $path = config('wordpress.remote.private_key_path');
        if ($path && is_readable($path)) {
            $contents = file_get_contents($path);
            if ($contents !== false) {
                return $contents;
            }
        }

        throw ValidationException::withMessages([
            'server_private_key' => 'Remote private key is not readable. Set WORDPRESS_REMOTE_PRIVATE_KEY or WORDPRESS_REMOTE_PRIVATE_KEY_PATH in .env.',
        ]);
    }
}
