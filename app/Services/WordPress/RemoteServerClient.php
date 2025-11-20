<?php

namespace App\Services\WordPress;

use App\Models\WordPressSite;
use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Exception\UnableToConnectException;
use phpseclib3\Net\SFTP;
use phpseclib3\Net\SSH2;
use RuntimeException;

class RemoteServerClient
{
    protected ?SSH2 $ssh = null;

    protected ?SFTP $sftp = null;

    public function __construct(
        protected readonly string $host,
        protected readonly int $port,
        protected readonly string $user,
        protected readonly string $authType,
        protected readonly ?string $password,
        protected readonly ?string $privateKey,
    ) {
    }

    public static function fromSite(WordPressSite $site): self
    {
        $credentials = $site->credentials();

        return new self(
            $credentials['host'],
            $credentials['port'] ?? 22,
            $credentials['user'],
            $credentials['auth_type'] ?? 'key',
            $credentials['password'],
            $credentials['private_key'],
        );
    }

    public function run(string $command): string
    {
        $ssh = $this->connectSSH();

        $output = $ssh->exec($command);

        if ($output === false) {
            throw new RuntimeException("Unable to execute remote command: {$command}");
        }

        return trim((string) $output);
    }

    public function ensureDirectory(string $path): void
    {
        $this->run(sprintf('mkdir -p %s', escapeshellarg($path)));
    }

    public function upload(string $remotePath, string $contents): void
    {
        $sftp = $this->connectSftp();

        if (! $sftp->put($remotePath, $contents)) {
            throw new RuntimeException("Failed to upload file to {$remotePath}");
        }
    }

    protected function connectSSH(): SSH2
    {
        if ($this->ssh?->isConnected()) {
            return $this->ssh;
        }

        $this->ssh = new SSH2($this->host, $this->port);

        if (! $this->authenticate($this->ssh)) {
            throw new UnableToConnectException("Unable to connect to {$this->host} via SSH");
        }

        return $this->ssh;
    }

    protected function connectSftp(): SFTP
    {
        if ($this->sftp?->isConnected()) {
            return $this->sftp;
        }

        $this->sftp = new SFTP($this->host, $this->port);

        if (! $this->authenticate($this->sftp)) {
            throw new UnableToConnectException("Unable to connect to {$this->host} via SFTP");
        }

        return $this->sftp;
    }

    protected function authenticate(SSH2|SFTP $client): bool
    {
        return match ($this->authType) {
            'password' => $client->login($this->user, $this->password ?? ''),
            default => $client->login($this->user, $this->resolveKey()),
        };
    }

    protected function resolveKey()
    {
        if (! $this->privateKey) {
            throw new RuntimeException('Missing private key credentials for SSH authentication.');
        }

        return PublicKeyLoader::load($this->privateKey);
    }
}
