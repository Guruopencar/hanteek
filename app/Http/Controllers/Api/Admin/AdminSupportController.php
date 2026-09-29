<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\ApiController;
use App\Models\AdminLog;
use App\Models\SupportTicket;
use App\Models\SupportTicketReply;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminSupportController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = SupportTicket::with(['user.profile', 'assignee.profile'])
            ->withCount('replies');

        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->priority) {
            $query->where('priority', $request->priority);
        }
        if ($request->assigned_to === 'me') {
            $query->where('assigned_to', $request->user()->id);
        }
        if ($request->q) {
            $q = $request->q;
            $query->where(function ($qq) use ($q) {
                $qq->where('subject', 'like', "%{$q}%")
                   ->orWhere('message', 'like', "%{$q}%");
            });
        }

        $tickets = $query->orderByRaw("FIELD(status, 'open','in_progress','resolved','closed')")
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data'    => $tickets->map(fn($t) => [
                'id'            => $t->id,
                'subject'       => $t->subject,
                'status'        => $t->status,
                'priority'      => $t->priority,
                'replies_count' => $t->replies_count,
                'user'          => [
                    'id'        => $t->user->id,
                    'full_name' => $t->user->profile?->full_name ?? $t->user->name,
                    'email'     => $t->user->email,
                    'avatar'    => $t->user->profile?->avatar,
                ],
                'assignee' => $t->assignee ? [
                    'id'        => $t->assignee->id,
                    'full_name' => $t->assignee->profile?->full_name ?? $t->assignee->name,
                ] : null,
                'created_at'  => $t->created_at->toISOString(),
                'resolved_at' => $t->resolved_at?->toISOString(),
            ]),
            'meta' => [
                'current_page' => $tickets->currentPage(),
                'last_page'    => $tickets->lastPage(),
                'total'        => $tickets->total(),
            ],
        ]);
    }

    public function show(SupportTicket $ticket): JsonResponse
    {
        $ticket->load(['user.profile', 'assignee.profile', 'replies.sender.profile']);

        return $this->success([
            'id'         => $ticket->id,
            'subject'    => $ticket->subject,
            'message'    => $ticket->message,
            'status'     => $ticket->status,
            'priority'   => $ticket->priority,
            'user'       => [
                'id'        => $ticket->user->id,
                'full_name' => $ticket->user->profile?->full_name ?? $ticket->user->name,
                'email'     => $ticket->user->email,
                'avatar'    => $ticket->user->profile?->avatar,
                'phone'     => $ticket->user->phone,
            ],
            'assignee' => $ticket->assignee ? [
                'id'        => $ticket->assignee->id,
                'full_name' => $ticket->assignee->profile?->full_name ?? $ticket->assignee->name,
            ] : null,
            'created_at'  => $ticket->created_at->toISOString(),
            'resolved_at' => $ticket->resolved_at?->toISOString(),
            'replies' => $ticket->replies->map(fn($r) => [
                'id'          => $r->id,
                'message'     => $r->message,
                'is_internal' => (bool) $r->is_internal,
                'sender_id'   => $r->sender_id,
                'sender'      => [
                    'id'        => $r->sender->id,
                    'full_name' => $r->sender->profile?->full_name ?? $r->sender->name,
                    'avatar'    => $r->sender->profile?->avatar,
                    'role'      => $r->sender->role,
                ],
                'created_at'  => $r->created_at->toISOString(),
            ])->values(),
        ]);
    }

    public function reply(Request $request, SupportTicket $ticket): JsonResponse
    {
        $data = $request->validate([
            'message'     => 'required|string|max:5000',
            'is_internal' => 'nullable|boolean',
        ]);

        $reply = SupportTicketReply::create([
            'ticket_id'   => $ticket->id,
            'sender_id'   => $request->user()->id,
            'message'     => $data['message'],
            'is_internal' => $data['is_internal'] ?? false,
        ]);

        if ($ticket->status === 'open') {
            $ticket->update(['status' => 'in_progress']);
        }

        AdminLog::record('reply_ticket', 'SupportTicket', $ticket->id);

        return $this->created($reply->load('sender.profile'), 'Відповідь надіслано');
    }

    public function updateStatus(Request $request, SupportTicket $ticket): JsonResponse
    {
        $data = $request->validate([
            'status' => 'required|in:open,in_progress,resolved,closed',
        ]);

        $updates = ['status' => $data['status']];
        if (in_array($data['status'], ['resolved', 'closed']) && !$ticket->resolved_at) {
            $updates['resolved_at'] = now();
        }
        if ($data['status'] === 'open' || $data['status'] === 'in_progress') {
            $updates['resolved_at'] = null;
        }

        $ticket->update($updates);
        AdminLog::record('update_ticket_status', 'SupportTicket', $ticket->id, $data['status']);

        return $this->success(null, 'Статус оновлено');
    }

    public function assign(Request $request, SupportTicket $ticket): JsonResponse
    {
        $data = $request->validate([
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $ticket->update(['assigned_to' => $data['assigned_to'] ?? null]);
        AdminLog::record('assign_ticket', 'SupportTicket', $ticket->id, (string) ($data['assigned_to'] ?? 'null'));

        return $this->success(null, 'Призначено');
    }

    public function stats(): JsonResponse
    {
        return $this->success([
            'open'        => SupportTicket::where('status', 'open')->count(),
            'in_progress' => SupportTicket::where('status', 'in_progress')->count(),
            'resolved'    => SupportTicket::where('status', 'resolved')->count(),
            'closed'      => SupportTicket::where('status', 'closed')->count(),
            'high'        => SupportTicket::whereIn('priority', ['high', 'urgent'])
                                          ->whereIn('status', ['open', 'in_progress'])
                                          ->count(),
        ]);
    }
}
