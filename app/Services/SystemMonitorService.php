<?php

namespace App\Services;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class SystemMonitorService
{
    public function overview(): array
    {
        $storagePath = storage_path();
        $diskFree = @disk_free_space($storagePath);
        $diskTotal = @disk_total_space($storagePath);

        return [
            'app_name' => config('app.name'),
            'app_env' => config('app.env'),
            'laravel_version' => app()->version(),
            'php_version' => PHP_VERSION,
            'maintenance_mode' => app()->isDownForMaintenance(),
            'maintenance_bypass_url' => $this->maintenanceBypassUrl(),
            'debug_mode' => (bool) config('app.debug'),
            'cache_driver' => config('cache.default'),
            'queue_driver' => config('queue.default'),
            'session_driver' => config('session.driver'),
            'database_connected' => $this->databaseConnected(),
            'memory_usage_mb' => round(memory_get_usage(true) / 1048576, 2),
            'memory_peak_mb' => round(memory_get_peak_usage(true) / 1048576, 2),
            'disk_free_gb' => $diskFree ? round($diskFree / 1073741824, 2) : null,
            'disk_total_gb' => $diskTotal ? round($diskTotal / 1073741824, 2) : null,
            'disk_used_percent' => ($diskFree && $diskTotal && $diskTotal > 0)
                ? round((($diskTotal - $diskFree) / $diskTotal) * 100, 1)
                : null,
            'log_file' => storage_path('logs/laravel.log'),
            'log_exists' => File::exists(storage_path('logs/laravel.log')),
            'log_size_bytes' => File::exists(storage_path('logs/laravel.log'))
                ? File::size(storage_path('logs/laravel.log'))
                : 0,
        ];
    }

    /**
     * @return list<string>
     */
    public function recentLogLines(int $lines = 100): array
    {
        $path = storage_path('logs/laravel.log');

        if (! File::exists($path)) {
            return ['No log file found at storage/logs/laravel.log.'];
        }

        $content = File::get($path);
        $allLines = preg_split("/\r\n|\n|\r/", $content) ?: [];
        $allLines = array_values(array_filter($allLines, fn ($line) => $line !== ''));

        return array_slice($allLines, -$lines);
    }

    public function runMaintenanceTask(string $task): string
    {
        return match ($task) {
            'cache' => $this->runArtisan('cache:clear'),
            'config' => $this->runArtisan('config:clear'),
            'views' => $this->runArtisan('view:clear'),
            'routes' => $this->runArtisan('route:clear'),
            'optimize' => $this->runArtisan('optimize:clear'),
            default => throw new \InvalidArgumentException('Unknown maintenance task.'),
        };
    }

    public function enableMaintenance(?string $message = null): ?string
    {
        $options = ['--retry' => 60];

        $secret = config('app.maintenance.secret');
        if ($secret) {
            $options['--secret'] = $secret;
        } else {
            $options['--with-secret'] = true;
        }

        Artisan::call('down', $options);

        if ($message) {
            $this->storeMaintenanceMessage($message);
        }

        return $this->maintenanceBypassPath();
    }

    public function disableMaintenance(): void
    {
        Artisan::call('up');
    }

    public function maintenanceBypassPath(): ?string
    {
        $path = storage_path('framework/down');

        if (! File::exists($path)) {
            return null;
        }

        $data = json_decode(File::get($path), true);

        if (! is_array($data) || empty($data['secret'])) {
            return null;
        }

        return (string) $data['secret'];
    }

    public function maintenanceBypassUrl(): ?string
    {
        $path = $this->maintenanceBypassPath();

        if (! $path) {
            return null;
        }

        return rtrim((string) config('app.url'), '/').'/'.ltrim($path, '/');
    }

    public function maintenanceMessage(): ?string
    {
        $path = storage_path('framework/down');

        if (! File::exists($path)) {
            return null;
        }

        $data = json_decode(File::get($path), true);

        if (! is_array($data) || empty($data['message'])) {
            return null;
        }

        return (string) $data['message'];
    }

    private function storeMaintenanceMessage(string $message): void
    {
        $path = storage_path('framework/down');

        if (! File::exists($path)) {
            return;
        }

        $data = json_decode(File::get($path), true);

        if (! is_array($data)) {
            return;
        }

        $data['message'] = $message;

        File::put($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    private function databaseConnected(): bool
    {
        try {
            DB::connection()->getPdo();

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    private function runArtisan(string $command): string
    {
        Artisan::call($command);

        return trim(Artisan::output()) ?: ucfirst(str_replace(':', ' ', $command)).' completed.';
    }
}
