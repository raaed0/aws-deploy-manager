## Simple WP Site Manager

A Laravel 12 + Inertia.js (React) dashboard for provisioning, operating, and monitoring containerized WordPress installations that live on remote VPS servers. Each site ships with full lifecycle management (deploy, start, stop, destroy), encrypted credentials, and a lightweight monitoring channel that keeps the UI in sync with Docker.

### Tech stack

- Laravel 12, PHP 8.2+ with database queues and encrypted Eloquent casts  
- Inertia.js with React 18, Tailwind CSS, Vite  
- phpseclib for SSH/SFTP automation against any Linux host with Docker  
- Docker Compose generated on the fly per site, using the official `wordpress:latest` and `mariadb:11` images

### Capabilities

- CRUD for WordPress sites with validation, status filtering, and live metrics.
- Remote orchestration layer that writes Docker Compose + `.env` files and executes `docker compose` commands over SSH.
- Background jobs (`sites` queue) for deploy/start/stop/destroy so long-running SSH tasks don’t block the UI.
- Monitoring webhook and `scripts/docker-monitor.sh` helper that can be installed on any server to report container health every five minutes.
- Database seeder and React UI kit for rapid local demos.

---

## Getting started

### Requirements

- PHP 8.2+, Composer, Node.js 20+, npm or pnpm  
- SQLite (default) or another database supported by Laravel  
- Docker is **not** required locally; remote servers just need Docker Engine + Compose plugin.

### Windows 10 quick start (no WSL)

Run PowerShell from the project root:

```powershell
# Temporarily allow the helper script to run in this shell
Set-ExecutionPolicy -Scope Process -ExecutionPolicy Bypass

# Install PHP/Composer/Node dependencies, create .env + SQLite DB, run migrations, and build assets
.\scripts\setup-windows.ps1

# Optional flags:
#   -SkipMigrate           -> install everything but skip migrate/seed
#   -SkipFrontendBuild     -> skip Vite build if you'll run npm run dev instead
```

Tips:

- Ensure PHP’s `sqlite3` **and** `pdo_sqlite` extensions are enabled in `php.ini` (the script checks these and fails fast if missing).
- `composer dev` works on Windows and starts the server, queue worker, logs, and Vite in one process group. Or run the commands manually: `php artisan serve`, `php artisan queue:listen --queue=sites`, and `npm run dev`.

### Installation

```bash
git clone git@github.com:<you>/simple-wp-site-manager.git
cd simple-wp-site-manager
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install
npm run dev
```

Run the app with:

```bash
php artisan serve
php artisan queue:listen --queue=sites
```

> The queue worker is required because deployments, starts, and stops all run asynchronously on the `sites` queue.

### Tests

```bash
php artisan test
```

Feature tests cover the monitoring webhook and the basic site provisioning workflow (ensuring jobs are queued and tokens are enforced).

---

## Remote server automation

1. Ensure Docker Engine and the Compose plugin are installed.  
2. Create a dedicated directory for sites (default `/opt/wp-sites`).  
3. Create a Docker network once: `docker network create wp-sites`. The app will also attempt to create it automatically if missing.
4. Copy `scripts/docker-monitor.sh` to the server, mark it executable, and set the environment variables the script expects:

```bash
scp scripts/docker-monitor.sh ubuntu@server:/usr/local/bin/docker-monitor
ssh ubuntu@server "chmod +x /usr/local/bin/docker-monitor"
```

Add the cron entry (`crontab -e`) so it runs every five minutes and logs to `/var/log/docker-monitor.log`:

```
MONITORING_ENDPOINT="https://app.example.com/api/site-status"
MONITORING_TOKEN="super-secret-token"
*/5 * * * * MONITORING_ENDPOINT=$MONITORING_ENDPOINT MONITORING_TOKEN=$MONITORING_TOKEN /usr/local/bin/docker-monitor
```

Set the exact same token inside the Laravel app (`MONITORING_ACCESS_TOKEN` in `.env`). Each run enumerates all Docker containers, maps Docker states to app statuses (running, deploying, stopped, failed), and POSTs to `/api/site-status`.

---

## Application configuration

- `WORDPRESS_DOCKER_IMAGE`, `WORDPRESS_DOCKER_NETWORK`, `WORDPRESS_DOCKER_PATH` – customize the default container template.
- `MONITORING_ENDPOINT` – URL hit by the remote cron job (usually your deployed `/api/site-status`).
- `MONITORING_ACCESS_TOKEN` – shared secret validated by `SiteStatusController`.
- Queue, cache, session, and DB drivers are all configured through the standard Laravel `.env` keys.

### Encryption

Server passwords, SSH keys, and database passwords are stored with Laravel’s `encrypted` cast automatically using `APP_KEY`. No plain text secrets ever touch the database.

### Docker template

`App\Services\WordPress\DockerComposeBuilder` is responsible for generating `docker-compose.yml` and `.env` files, including:

- WordPress container (`wordpress:latest` by default)  
- Dedicated MariaDB container per site  
- Volume mounts for persistence  
- Arbitrary environment overrides (WP constants, custom configs)  
- Deterministic high host ports (8000–8999) derived from the container name so multiple sites can coexist on one server

`WordPressDeploymentService` uploads those files via SFTP, ensures the Docker network exists, and executes `docker compose pull/up/down` commands based on lifecycle events.

---

## UI walkthrough

- **Sites overview:** Search and filter by status, see live counts, and trigger start/stop/delete actions without leaving the table.
- **Create / Edit:** React form powered by Inertia with dynamic auth-type fields (SSH key vs password), environment variable builder, and helpful copy explaining what’s deployed.
- **Flash + progress:** Global banner surfaces queue activity, while Inertia Progress keeps navigation responsive.

---

## Monitoring API

`POST /api/site-status`

| Field      | Description                                            |
| ---------- | ------------------------------------------------------ |
| container  | Container name or domain matching a saved WordPress site |
| status     | `running`, `stopped`, `deploying`, or `failed`         |
| uptime     | Optional timestamp or human-readable uptime string     |
| message    | Optional message that will be stored on the site meta  |

Headers:

```
X-Monitor-Token: <MONITORING_ACCESS_TOKEN>
```

If the token is missing or invalid the request is rejected with `403`.

---

## Repo tasks checklist

- [x] Laravel + Inertia scaffold with Tailwind/React.
- [x] WordPress site model, migration, factory, and seeder with encrypted credentials.
- [x] SSH/Docker orchestration service + queueable jobs for deploy/start/stop/destroy.
- [x] Monitoring webhook + cron-friendly Bash script that logs to `/var/log/docker-monitor.log`.
- [x] React dashboard with CRUD flows, status filtering, and polished UI.
- [x] Documentation + feature tests.

Enjoy building and extending your own lightweight WP operations hub!
