<?php

namespace App\Http\Controllers;

use App\Models\AdminAction;
use App\Models\DatabaseBackup;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\DatabaseBackupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminBackupController extends Controller
{
    public function __construct(
        private DatabaseBackupService $backups
    ) {}

    public function index()
    {
        $backupSettings = SystemSetting::query()
            ->where('group', 'Backup')
            ->orderBy('key')
            ->get();

        return view('admin.backups.index', [
            'backups' => DatabaseBackup::with('creator')->latest()->paginate(20),
            'backupSettings' => $backupSettings,
            'autoEnabled' => (bool) SystemSetting::getValue('backup_auto_enabled', false),
            'scheduleFrequency' => SystemSetting::getValue('backup_schedule_frequency', 'daily'),
            'retentionDays' => (int) SystemSetting::getValue('backup_retention_days', 14),
        ]);
    }

    public function store(Request $request)
    {
        try {
            $backup = $this->backups->createBackup(false, $request->user()->id);
            $this->logAdminAction('backup_create', null, null, [
                'backup_id' => $backup->id,
                'filename' => $backup->filename,
            ]);

            return back()->with('status', 'Database backup created: '.$backup->filename);
        } catch (\Throwable $e) {
            report($e);

            return back()->withErrors(['backup' => 'Backup failed: '.$e->getMessage()]);
        }
    }

    public function restore(Request $request, DatabaseBackup $backup)
    {
        $request->validate([
            'confirm' => ['required', 'in:RESTORE'],
        ]);

        try {
            $this->backups->restoreBackup($backup);
            $this->logAdminAction('backup_restore', null, 'Database restored from backup', [
                'backup_id' => $backup->id,
                'filename' => $backup->filename,
            ]);

            return back()->with('status', 'Database restored from '.$backup->filename.'.');
        } catch (\Throwable $e) {
            report($e);

            return back()->withErrors(['backup' => 'Restore failed: '.$e->getMessage()]);
        }
    }

    public function destroy(DatabaseBackup $backup)
    {
        $filename = $backup->filename;
        $this->backups->deleteBackup($backup);
        $this->logAdminAction('backup_delete', null, null, ['filename' => $filename]);

        return back()->with('status', 'Backup deleted: '.$filename);
    }

    public function download(DatabaseBackup $backup)
    {
        if ($backup->status !== 'completed') {
            return back()->withErrors(['backup' => 'Only completed backups can be downloaded.']);
        }

        $fullPath = storage_path('app/'.$backup->path);

        if (! file_exists($fullPath)) {
            return back()->withErrors(['backup' => 'Backup file not found on disk.']);
        }

        return response()->download($fullPath, $backup->filename);
    }

    public function updateSchedule(Request $request)
    {
        $data = $request->validate([
            'settings' => ['array'],
        ]);

        foreach ($data['settings'] ?? [] as $key => $value) {
            $setting = SystemSetting::query()->where('key', $key)->first();

            if (! $setting || $setting->group !== 'Backup') {
                continue;
            }

            $setting->value = $value;
            $setting->save();
        }

        $this->logAdminAction('backup_schedule_update');

        return back()->with('status', 'Automatic backup schedule updated.');
    }

    private function logAdminAction(string $action, ?User $targetUser = null, ?string $reason = null, array $meta = []): void
    {
        $admin = Auth::user();

        if (! $admin || $admin->role !== User::ROLE_ADMIN) {
            return;
        }

        try {
            AdminAction::create([
                'admin_id' => $admin->id,
                'target_user_id' => $targetUser?->id,
                'action' => $action,
                'reason' => $reason,
                'meta' => $meta ?: null,
                'ip_address' => request()->ip(),
            ]);
        } catch (\Throwable) {
            //
        }
    }
}
