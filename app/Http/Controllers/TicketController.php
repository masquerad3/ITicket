<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TicketController extends Controller
{
    private function normalizeTicketTimestamps(object $ticketRow): object
    {
        foreach (['created_at', 'updated_at'] as $field) {
            if (!isset($ticketRow->{$field}) || $ticketRow->{$field} === null || $ticketRow->{$field} === '') {
                continue;
            }

            try {
                $ticketRow->{$field} = Carbon::parse((string) $ticketRow->{$field});
            } catch (\Throwable) {
                // Leave as-is if parsing fails.
            }
        }

        return $ticketRow;
    }

    private function isStaff(): bool
    {
        $role = strtolower((string) (auth()->user()?->role ?? 'user'));

        return in_array($role, ['it', 'admin'], true);
    }

    public function index(Request $request)
    {
        $view = 'my';

        if ($this->isStaff()) {
            $candidate = strtolower((string) $request->query('view', 'queue'));
            $view = in_array($candidate, ['queue', 'mine', 'all'], true) ? $candidate : 'queue';
        }

        if (!$this->isStaff()) {
            $tickets = collect(DB::select('EXEC dbo.sp_read_tickets_by_user @user_id = ?', [auth()->id()]));
        } else {
            try {
                if ($view === 'all') {
                    $tickets = collect(DB::select('EXEC dbo.sp_read_all_tickets'));
                } elseif ($view === 'mine') {
                    $tickets = collect(DB::select('EXEC dbo.sp_read_tickets_assigned_to_user @assigned_to = ?', [auth()->id()]));
                } else {
                    // queue = unassigned + assigned-to-me
                    $unassigned = collect(DB::select('EXEC dbo.sp_read_unassigned_tickets'));
                    $mine = collect(DB::select('EXEC dbo.sp_read_tickets_assigned_to_user @assigned_to = ?', [auth()->id()]));
                    $tickets = $unassigned->concat($mine)->unique('ticket_id')->values();
                }
            } catch (\Throwable) {
                // If the queue procedures haven't been installed yet, fall back safely.
                $tickets = collect(DB::select('EXEC dbo.sp_read_all_tickets'));
                $view = 'all';
            }
        }

        $tickets = $tickets
            ->map(fn ($row) => $this->normalizeTicketTimestamps($row))
            ->values();

        $counts = [
            'total' => $tickets->count(),
            'open' => $tickets->where('status', 'open')->count(),
            'progress' => $tickets->where('status', 'in_progress')->count(),
            'resolved' => $tickets->where('status', 'resolved')->count(),
        ];

        return view('pages.tickets', compact('tickets', 'counts', 'view'));
    }

    public function create()
    {
        return view('pages.create-ticket');
    }

    public function show(int $ticket)
    {
        $rows = DB::select('EXEC dbo.sp_read_ticket_by_id @ticket_id = ?', [$ticket]);
        $row = $rows[0] ?? null;

        if (!$row) {
            abort(404);
        }

        if (!$this->isStaff() && (int) $row->user_id !== (int) auth()->id()) {
            abort(404);
        }

        $row = $this->normalizeTicketTimestamps($row);

        $attachments = [];
        if (isset($row->attachments) && $row->attachments !== null && $row->attachments !== '') {
            $decoded = json_decode((string) $row->attachments, true);
            if (is_array($decoded)) {
                $attachments = $decoded;
            }
        }
        $row->attachments = $attachments;

        $messages = collect();
        try {
            $includeInternal = $this->isStaff() ? 1 : 0;
            $messages = collect(DB::select(
                'EXEC dbo.sp_read_ticket_messages_by_ticket @ticket_id = ?, @include_internal = ?',
                [$ticket, $includeInternal]
            ))
                ->map(function ($m) {
                    foreach (['created_at'] as $field) {
                        if (!isset($m->{$field}) || $m->{$field} === null || $m->{$field} === '') {
                            continue;
                        }
                        try {
                            $m->{$field} = Carbon::parse((string) $m->{$field});
                        } catch (\Throwable) {
                            // no-op
                        }
                    }

                    return $m;
                })
                ->values();
        } catch (\Throwable) {
            $messages = collect();
        }

        return view('pages.ticket', ['ticket' => $row, 'messages' => $messages]);
    }

    public function storeMessage(Request $request, int $ticket): RedirectResponse
    {
        $rows = DB::select('EXEC dbo.sp_read_ticket_by_id @ticket_id = ?', [$ticket]);
        $row = $rows[0] ?? null;

        if (!$row) {
            abort(404);
        }

        if (!$this->isStaff() && (int) $row->user_id !== (int) auth()->id()) {
            abort(404);
        }

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
            'message_type' => ['nullable', 'string', 'in:public,internal'],
            'next_status' => ['nullable', 'string', 'in:open,in_progress,resolved,closed'],
        ]);

        $messageType = $validated['message_type'] ?? 'public';
        if (!$this->isStaff()) {
            $messageType = 'public';
        }

        DB::select(
            'EXEC dbo.sp_create_ticket_message @ticket_id = ?, @user_id = ?, @message_type = ?, @body = ?',
            [$ticket, auth()->id(), $messageType, $validated['body']]
        );

        if ($this->isStaff() && !empty($validated['next_status'])) {
            DB::select(
                'EXEC dbo.sp_update_ticket_status @ticket_id=?, @status=?',
                [$ticket, $validated['next_status']]
            );
        }

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('status', 'Message sent.');
    }

    public function assignToMe(int $ticket): RedirectResponse
    {
        if (!$this->isStaff()) {
            abort(403);
        }

        DB::select(
            'EXEC dbo.sp_assign_ticket_to_user @ticket_id=?, @assigned_to=?',
            [$ticket, auth()->id()]
        );

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('status', 'Ticket assigned successfully.');
    }

    public function updateStatus(Request $request, int $ticket): RedirectResponse
    {
        if (!$this->isStaff()) {
            abort(403);
        }

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:open,in_progress,resolved,closed'],
        ]);

        DB::select(
            'EXEC dbo.sp_update_ticket_status @ticket_id=?, @status=?',
            [$ticket, $validated['status']]
        );

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('status', 'Ticket status updated.');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:50'],
            'priority' => ['required', 'string', 'in:Low,Medium,High'],
            'department' => ['nullable', 'string', 'max:100'],
            'location' => ['nullable', 'string', 'max:100'],
            'description' => ['required', 'string', 'max:1000'],
            'contact' => ['required', 'string', 'in:email,phone,teams'],
            'consent' => ['accepted'],
            'files' => ['nullable', 'array'],
            'files.*' => ['file', 'max:10240', 'mimes:png,jpg,jpeg,pdf,doc,docx,txt'],
        ]);

        $created = DB::select(
            'EXEC dbo.sp_create_ticket @user_id=?, @subject=?, @category=?, @priority=?, @department=?, @location=?, @description=?, @preferred_contact=?, @status=?',
            [
                auth()->id(),
                $validated['subject'],
                $validated['category'],
                $validated['priority'],
                $validated['department'] ?? null,
                $validated['location'] ?? null,
                $validated['description'],
                $validated['contact'],
                'open',
            ]
        );

        $ticketId = (int) (($created[0]->ticket_id ?? 0));
        if ($ticketId <= 0) {
            abort(500, 'Failed to create ticket.');
        }

        $storedPaths = [];
        foreach ($request->file('files', []) as $file) {
            $storedPaths[] = Storage::disk('public')->putFile("tickets/{$ticketId}", $file);
        }

        if (!empty($storedPaths)) {
            DB::select(
                'EXEC dbo.sp_update_ticket_attachments @ticket_id=?, @attachments=?',
                [$ticketId, json_encode($storedPaths)]
            );
        }

        return redirect()
            ->route('tickets.index')
            ->with('status', 'Ticket submitted successfully.');
    }
}
