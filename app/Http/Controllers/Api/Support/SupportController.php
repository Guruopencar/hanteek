<?php

namespace App\Http\Controllers\Api\Support;

use App\Http\Controllers\Api\ApiController;
use App\Models\SupportTicket;
use App\Models\SupportTicketReply;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupportController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $tickets = SupportTicket::where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data'    => $tickets->map(fn($t) => [
                'id'          => $t->id,
                'subject'     => $t->subject,
                'status'      => $t->status,
                'priority'    => $t->priority,
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

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'subject'  => 'required|string|max:300',
            'message'  => 'required|string|max:5000',
            'priority' => 'nullable|in:low,normal,high,urgent',
        ]);

        $ticket = SupportTicket::create([
            ...$data,
            'user_id' => $request->user()->id,
            'status'  => 'open',
        ]);

        return $this->created($ticket, 'Тікет створено');
    }

    public function show(Request $request, SupportTicket $ticket): JsonResponse
    {
        if ($ticket->user_id !== $request->user()->id) {
            return $this->forbidden();
        }

        $ticket->load(['replies.sender.profile']);

        return $this->success([
            'id'          => $ticket->id,
            'subject'     => $ticket->subject,
            'message'     => $ticket->message,
            'status'      => $ticket->status,
            'priority'    => $ticket->priority,
            'created_at'  => $ticket->created_at->toISOString(),
            'resolved_at' => $ticket->resolved_at?->toISOString(),
            'replies'     => $ticket->replies->where('is_internal', false)->map(fn($r) => [
                'id'         => $r->id,
                'message'    => $r->message,
                'sender'     => $r->sender->profile?->full_name ?? $r->sender->name,
                'created_at' => $r->created_at->toISOString(),
            ]),
        ]);
    }

    public function reply(Request $request, SupportTicket $ticket): JsonResponse
    {
        if ($ticket->user_id !== $request->user()->id) {
            return $this->forbidden();
        }

        $data = $request->validate(['message' => 'required|string|max:3000']);

        $reply = SupportTicketReply::create([
            'ticket_id' => $ticket->id,
            'sender_id' => $request->user()->id,
            'message'   => $data['message'],
        ]);

        if ($ticket->status === 'resolved') {
            $ticket->update(['status' => 'open']);
        }

        return $this->created($reply, 'Відповідь надіслано');
    }
}
