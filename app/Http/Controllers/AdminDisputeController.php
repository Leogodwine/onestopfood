<?php

namespace App\Http\Controllers;

use App\Models\Dispute;
use App\Models\AdminAction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminDisputeController extends Controller
{
    public function index(Request $request)
    {
        $status = (string) $request->query('status', '');

        $query = Dispute::with(['order', 'payment', 'createdBy', 'resolvedBy'])
            ->latest();

        if ($status !== '') {
            $query->where('status', $status);
        }

        $disputes = $query->paginate(20)->withQueryString();

        return view('admin.disputes', [
            'disputes' => $disputes,
            'status' => $status,
        ]);
    }

    public function show(Dispute $dispute)
    {
        $dispute->load(['order', 'payment', 'createdBy', 'resolvedBy']);

        return view('admin.dispute-show', [
            'dispute' => $dispute,
        ]);
    }

    public function resolve(Request $request, Dispute $dispute)
    {
        $data = $request->validate([
            'status' => ['required', 'string', 'in:resolved,escalated,in_review'],
            'resolution_notes' => ['required', 'string', 'max:5000'],
        ]);

        $dispute->status = $data['status'];
        $dispute->resolution_notes = $data['resolution_notes'];
        $dispute->resolved_by_admin_id = $request->user()->id;
        $dispute->save();

        $this->logAdminAction('dispute_resolve', $dispute);

        return back()->with('status', 'Dispute updated.');
    }

    private function logAdminAction(string $action, Dispute $dispute): void
    {
        $admin = Auth::user();

        if (! $admin || $admin->role !== 'admin') {
            return;
        }

        try {
            AdminAction::create([
                'admin_id' => $admin->id,
                'target_user_id' => $dispute->created_by_user_id,
                'action' => $action,
                'reason' => $dispute->resolution_notes,
                'meta' => [
                    'dispute_id' => $dispute->id,
                    'status' => $dispute->status,
                ],
                'ip_address' => request()->ip(),
            ]);
        } catch (\Throwable $e) {
            // ignore
        }
    }
}

