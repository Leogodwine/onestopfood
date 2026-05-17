<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserVerificationDocument;
use App\Models\AdminAction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminVerificationController extends Controller
{
    public function index(Request $request)
    {
        $status = (string) $request->query('status', 'pending');
        $role = (string) $request->query('role', '');
        $type = (string) $request->query('type', '');

        $query = UserVerificationDocument::query()
            ->with('user')
            ->orderByDesc('created_at');

        if ($status !== '') {
            $query->where('status', $status);
        }

        if ($role !== '') {
            $query->whereHas('user', function ($q) use ($role) {
                $q->where('role', $role);
            });
        }

        if ($type !== '') {
            $query->where('type', $type);
        }

        $documents = $query->paginate(20)->withQueryString();

        $pendingCount = UserVerificationDocument::where('status', 'pending')->count();
        $expiringSoonCount = UserVerificationDocument::whereNotNull('expires_at')
            ->whereBetween('expires_at', [now(), now()->addDays(30)])
            ->count();

        return view('admin.verifications', [
            'documents' => $documents,
            'status' => $status,
            'role' => $role,
            'type' => $type,
            'pendingCount' => $pendingCount,
            'expiringSoonCount' => $expiringSoonCount,
        ]);
    }

    public function approve(Request $request, UserVerificationDocument $document)
    {
        $data = $request->validate([
            'admin_notes' => ['nullable', 'string', 'max:2000'],
            'expires_at' => ['nullable', 'date'],
        ]);

        $document->status = 'approved';
        $document->admin_notes = $data['admin_notes'] ?? null;
        $document->expires_at = $data['expires_at'] ?? null;
        $document->save();

        // Optionally mark user as approved if all docs are approved and user was pending
        $user = $document->user;
        if ($user && $user->status === User::STATUS_PENDING) {
            $pendingDocs = $user->verificationDocuments()->where('status', 'pending')->count();
            if ($pendingDocs === 0) {
                $user->status = User::STATUS_APPROVED;
                $user->approved_at = now();
                $user->save();
            }
        }

        $this->logAdminAction('verify_document_approve', $document);

        return back()->with('status', 'Verification document approved.');
    }

    public function reject(Request $request, UserVerificationDocument $document)
    {
        $data = $request->validate([
            'admin_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $document->status = 'rejected';
        $document->admin_notes = $data['admin_notes'] ?? null;
        $document->save();

        $this->logAdminAction('verify_document_reject', $document);

        return back()->with('status', 'Verification document rejected.');
    }

    public function requestMore(Request $request, UserVerificationDocument $document)
    {
        $data = $request->validate([
            'admin_notes' => ['required', 'string', 'max:2000'],
        ]);

        // Keep status as pending but update notes to request more docs
        $document->admin_notes = $data['admin_notes'];
        $document->save();

        $this->logAdminAction('verify_document_request_more', $document);

        return back()->with('status', 'Request for additional documents recorded.');
    }

    private function logAdminAction(string $action, UserVerificationDocument $document): void
    {
        $admin = Auth::user();

        if (! $admin || $admin->role !== User::ROLE_ADMIN) {
            return;
        }

        try {
            AdminAction::create([
                'admin_id' => $admin->id,
                'target_user_id' => $document->user_id,
                'action' => $action,
                'reason' => $document->admin_notes,
                'meta' => [
                    'document_id' => $document->id,
                    'type' => $document->type,
                    'status' => $document->status,
                ],
                'ip_address' => $request->ip() ?? null,
            ]);
        } catch (\Throwable $e) {
            // Do not break main flow if logging fails
        }
    }
}

