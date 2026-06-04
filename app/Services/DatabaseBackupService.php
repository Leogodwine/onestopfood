<?php

namespace App\Services;

use App\Models\DatabaseBackup;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

class DatabaseBackupService
{
    public function createBackup(bool $automatic = false, ?int $userId = null): DatabaseBackup
    {
        $filename = 'backup_'.now()->format('Y-m-d_His').'.sql';

        $backup = DatabaseBackup::create([
            'filename' => $filename,
            'path' => 'backups/'.$filename,
            'status' => 'in_progress',
            'automatic' => $automatic,
            'created_by' => $userId,
        ]);

        try {
            $directory = storage_path('app/backups');

            if (! File::isDirectory($directory)) {
                File::makeDirectory($directory, 0755, true);
            }

            $fullPath = storage_path('app/'.$backup->path);

            if (! $this->runMysqldump($fullPath)) {
                $this->runPhpBackup($fullPath);
            }

            if (! File::exists($fullPath) || File::size($fullPath) === 0) {
                throw new \RuntimeException('Backup file was not created or is empty.');
            }

            $backup->update([
                'size_bytes' => File::size($fullPath),
                'status' => 'completed',
                'completed_at' => now(),
            ]);
        } catch (\Throwable $e) {
            if (File::exists(storage_path('app/'.$backup->path))) {
                File::delete(storage_path('app/'.$backup->path));
            }

            $backup->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            throw $e;
        }

        return $backup->fresh();
    }

    public function restoreBackup(DatabaseBackup $backup): void
    {
        if ($backup->status !== 'completed') {
            throw new \RuntimeException('Only completed backups can be restored.');
        }

        $fullPath = storage_path('app/'.$backup->path);

        if (! File::exists($fullPath)) {
            throw new \RuntimeException('Backup file not found on disk.');
        }

        if (! $this->runMysqlImport($fullPath)) {
            throw new \RuntimeException('Database restore requires the MySQL client (mysql). Set MYSQL_PATH in your environment.');
        }
    }

    public function deleteBackup(DatabaseBackup $backup): void
    {
        $fullPath = storage_path('app/'.$backup->path);

        if (File::exists($fullPath)) {
            File::delete($fullPath);
        }

        $backup->delete();
    }

    public function shouldRunScheduledBackup(): bool
    {
        if (! filter_var($this->setting('backup_auto_enabled', false), FILTER_VALIDATE_BOOL)) {
            return false;
        }

        $frequency = $this->setting('backup_schedule_frequency', 'daily');
        $lastBackup = DatabaseBackup::query()
            ->where('automatic', true)
            ->where('status', 'completed')
            ->latest('completed_at')
            ->first();

        if (! $lastBackup?->completed_at) {
            return true;
        }

        return match ($frequency) {
            'weekly' => $lastBackup->completed_at->lte(now()->subWeek()),
            default => $lastBackup->completed_at->lte(now()->subDay()),
        };
    }

    public function pruneOldBackups(): int
    {
        $retentionDays = max(1, (int) $this->setting('backup_retention_days', 14));
        $cutoff = now()->subDays($retentionDays);
        $removed = 0;

        DatabaseBackup::query()
            ->where('created_at', '<', $cutoff)
            ->orderBy('id')
            ->each(function (DatabaseBackup $backup) use (&$removed) {
                $this->deleteBackup($backup);
                $removed++;
            });

        return $removed;
    }

    private function runMysqldump(string $fullPath): bool
    {
        $binary = $this->resolveBinary('MYSQLDUMP_PATH', ['mysqldump', 'C:\\xampp\\mysql\\bin\\mysqldump.exe']);

        if (! $binary) {
            return false;
        }

        $connection = config('database.connections.'.config('database.default'));
        $command = [
            $binary,
            '--host='.($connection['host'] ?? '127.0.0.1'),
            '--port='.($connection['port'] ?? '3306'),
            '--user='.($connection['username'] ?? 'root'),
            '--result-file='.$fullPath,
            '--single-transaction',
            '--routines',
            '--triggers',
            $connection['database'],
        ];

        if (! empty($connection['password'])) {
            $command[] = '--password='.$connection['password'];
        }

        $process = new Process($command);
        $process->setTimeout(600);
        $process->run();

        return $process->isSuccessful() && File::exists($fullPath) && File::size($fullPath) > 0;
    }

    private function runMysqlImport(string $fullPath): bool
    {
        $binary = $this->resolveBinary('MYSQL_PATH', ['mysql', 'C:\\xampp\\mysql\\bin\\mysql.exe']);

        if (! $binary) {
            return false;
        }

        $connection = config('database.connections.'.config('database.default'));
        $command = [
            $binary,
            '--host='.($connection['host'] ?? '127.0.0.1'),
            '--port='.($connection['port'] ?? '3306'),
            '--user='.($connection['username'] ?? 'root'),
            $connection['database'],
        ];

        if (! empty($connection['password'])) {
            $command[] = '--password='.$connection['password'];
        }

        $process = new Process($command);
        $process->setInput(File::get($fullPath));
        $process->setTimeout(600);
        $process->run();

        return $process->isSuccessful();
    }

    private function runPhpBackup(string $fullPath): void
    {
        $pdo = DB::connection()->getPdo();
        $database = config('database.connections.'.config('database.default').'.database');
        $tableKey = 'Tables_in_'.$database;
        $tables = DB::select('SHOW TABLES');

        $sql = "-- One Stop database backup\n";
        $sql .= '-- Generated: '.now()->toDateTimeString()."\n\n";
        $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        foreach ($tables as $tableRow) {
            $tableName = $tableRow->{$tableKey};
            $createRow = DB::select('SHOW CREATE TABLE `'.$tableName.'`')[0];
            $createSql = $createRow->{'Create Table'};

            $sql .= 'DROP TABLE IF EXISTS `'.$tableName."`;\n";
            $sql .= $createSql.";\n\n";

            DB::table($tableName)->orderBy(DB::raw('1'))->chunk(200, function ($rows) use (&$sql, $tableName, $pdo) {
                foreach ($rows as $row) {
                    $columns = array_keys((array) $row);
                    $values = array_map(
                        fn ($value) => $value === null ? 'NULL' : $pdo->quote((string) $value),
                        array_values((array) $row)
                    );

                    $sql .= 'INSERT INTO `'.$tableName.'` (`'.implode('`,`', $columns).'`) VALUES ('.implode(',', $values).");\n";
                }
            });

            $sql .= "\n";
        }

        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";

        File::put($fullPath, $sql);
    }

    /**
     * @param  list<string>  $fallbacks
     */
    private function resolveBinary(string $envKey, array $fallbacks): ?string
    {
        $candidates = array_filter([
            env($envKey),
            ...$fallbacks,
        ]);

        foreach ($candidates as $candidate) {
            if ($candidate === 'mysqldump' || $candidate === 'mysql') {
                $process = new Process([$candidate, '--version']);
                $process->run();

                if ($process->isSuccessful()) {
                    return $candidate;
                }

                continue;
            }

            if (File::exists($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    private function setting(string $key, mixed $default = null): mixed
    {
        if (! Schema::hasTable('system_settings')) {
            return $default;
        }

        try {
            return SystemSetting::getValue($key, $default);
        } catch (\Throwable) {
            return $default;
        }
    }
}
