<?php

namespace App\Http\Controllers;

use App\Models\AdminAction;
use App\Models\User;
use App\Services\SystemMonitorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminSystemController extends Controller
{
    public function __construct(
        private SystemMonitorService $monitor
    ) {}

    public function index()
    {
        return view('admin.system.index', [
            'overview' => $this->monitor->overview(),
            'logLines' => $this->monitor->recentLogLines(150),
        ]);
    }

    public function maintenance(Request $request)
    {
        $data = $request->validate([
            'action' => ['required', 'in:enable,disable'],
            'message' => ['nullable', 'string', 'max:500'],
        ]);

        if ($data['action'] === 'enable') {
            $bypassPath = $this->monitor->enableMaintenance($data['message'] ?? null);
            $this->logAdminAction('maintenance_enable', null, $data['message'] ?? null, [
                'bypass_path' => $bypassPath,
            ]);
            $message = 'Maintenance mode enabled. Visitors will see the maintenance page.';
            if ($bypassPath) {
                $message .= ' Admin bypass URL: '.url($bypassPath);
            }
        } else {
            $this->monitor->disableMaintenance();
            $this->logAdminAction('maintenance_disable');
            $message = 'Maintenance mode disabled. The site is live again.';
        }

        return back()->with('status', $message);
    }

    public function runTask(Request $request)
    {
        $data = $request->validate([
            'task' => ['required', 'in:cache,config,views,routes,optimize'],
        ]);

        $output = $this->monitor->runMaintenanceTask($data['task']);
        $this->logAdminAction('maintenance_task', null, null, ['task' => $data['task']]);

        return back()->with('status', $output);
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
