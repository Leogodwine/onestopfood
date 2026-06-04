<?php

namespace App\Console\Commands;

use App\Services\DatabaseBackupService;
use Illuminate\Console\Command;

class RunScheduledDatabaseBackup extends Command
{
    protected $signature = 'backup:database {--force : Run even when automatic backups are disabled}';

    protected $description = 'Create a scheduled database backup when enabled in system settings';

    public function handle(DatabaseBackupService $backups): int
    {
        if (! $this->option('force') && ! $backups->shouldRunScheduledBackup()) {
            $this->info('Automatic backup is disabled or not due yet.');

            return self::SUCCESS;
        }

        try {
            $backup = $backups->createBackup(true);
            $removed = $backups->pruneOldBackups();

            $this->info("Backup created: {$backup->filename}");
            $this->info("Removed {$removed} old backup(s).");

            return self::SUCCESS;
        } catch (\Throwable $e) {
            report($e);
            $this->error('Backup failed: '.$e->getMessage());

            return self::FAILURE;
        }
    }
}
