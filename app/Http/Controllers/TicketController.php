<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TicketController extends Controller
{
    private function createSystemMessage(int $ticketId, string $body): void
    {
        try {
            DB::select(
                'EXEC dbo.sp_create_ticket_message @ticket_id = ?, @user_id = ?, @message_type = ?, @body = ?',
                [$ticketId, auth()->id(), 'system', $body]
            );
        } catch (\Throwable) {
            // no-op
        }
    }

    private function parseDbDatetime(mixed $value): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }

        $appTz = (string) (config('app.timezone') ?: 'UTC');
        $dbTz = (string) (config('database.connections.sqlsrv.timezone') ?: $appTz);

        try {
            if ($value instanceof \DateTimeInterface) {
                return Carbon::instance($value)->setTimezone($appTz);
            }

            // SQL Server returns a timezone-less string; treat it as DB time (UTC by default), then convert.
            return Carbon::parse((string) $value, $dbTz)->setTimezone($appTz);
        } catch (\Throwable) {
            return null;
        }
    }

    private function normalizeTicketTimestamps(object $ticketRow): object
    {
        foreach (['created_at', 'updated_at', 'assigned_at', 'resolved_at'] as $field) {
            if (!isset($ticketRow->{$field}) || $ticketRow->{$field} === null || $ticketRow->{$field} === '') {
                continue;
            }

            $parsed = $this->parseDbDatetime($ticketRow->{$field});
            if ($parsed !== null) {
                $ticketRow->{$field} = $parsed;
            }
        }

        return $ticketRow;
    }

    private function isStaff(): bool
    {
        $role = strtolower((string) (auth()->user()?->role ?? 'user'));

        return in_array($role, ['it', 'admin'], true);
    }

    private function getAuthorizedTicketRow(int $ticket): object
    {
        $rows = DB::select('EXEC dbo.sp_read_ticket_by_id @ticket_id = ?', [$ticket]);
        $row = $rows[0] ?? null;

        if (!$row) {
            abort(404);
        }

        if (!$this->isStaff() && (int) $row->user_id !== (int) auth()->id()) {
            abort(404);
        }

        return $row;
    }

    private function inlineFileResponse(string $storedPath, ?string $filename = null, ?string $mime = null): BinaryFileResponse
    {
        $disk = Storage::disk('public');

        if ($storedPath === '' || !$disk->exists($storedPath)) {
            abort(404);
        }

        $absolutePath = $disk->path($storedPath);
        $filename = $filename ?: basename($storedPath);
        $detectedMime = null;
        if ($disk instanceof FilesystemAdapter) {
            $detectedMime = $disk->mimeType($storedPath);
        }

        $mime = $mime ?: ($detectedMime ?: 'application/octet-stream');

        return response()->file($absolutePath, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="'.str_replace('"', '', $filename).'"',
        ]);
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
                    $unassigned = collect(DB::select('EXEC dbo.sp_read_unassigned_tickets'));
                    $mine = collect(DB::select('EXEC dbo.sp_read_tickets_assigned_to_user @assigned_to = ?', [auth()->id()]));
                    $tickets = $unassigned->concat($mine)->unique('ticket_id')->values();
                }
            } catch (\Throwable) {
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
        $row = $this->getAuthorizedTicketRow($ticket);
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
                    if (isset($m->created_at) && $m->created_at !== null && $m->created_at !== '') {
                        $parsed = $this->parseDbDatetime($m->created_at);
                        if ($parsed !== null) {
                            $m->created_at = $parsed;
                        }
                    }
                    return $m;
                })
                ->values();
        } catch (\Throwable) {
            $messages = collect();
        }

        $files = collect();
        $messageFiles = collect();
        try {
            $all = collect(DB::select('EXEC dbo.sp_read_ticket_attachments_by_ticket @ticket_id = ?', [$ticket]))
                ->map(function ($f) {
                    if (isset($f->created_at) && $f->created_at !== null && $f->created_at !== '') {
                        $parsed = $this->parseDbDatetime($f->created_at);
                        if ($parsed !== null) {
                            $f->created_at = $parsed;
                        }
                    }

                    return $f;
                })
                ->values();

            $files = $all
                ->filter(fn ($a) => !isset($a->message_id) || $a->message_id === null)
                ->values();

            $messageFiles = $all
                ->filter(fn ($a) => isset($a->message_id) && $a->message_id !== null)
                ->values();
        } catch (\Throwable) {
            $files = collect();
            $messageFiles = collect();
        }

        if ($messages->count() > 0 && $messageFiles->count() > 0) {
            $byMessageId = $messageFiles->groupBy('message_id');
            $messages = $messages
                ->map(function ($m) use ($byMessageId) {
                    $m->files = ($byMessageId->get($m->message_id) ?? collect())->values();
                    return $m;
                })
                ->values();
        }

        $tags = collect();
        try {
            $tags = collect(DB::select('EXEC dbo.sp_read_ticket_tags_by_ticket @ticket_id = ?', [$ticket]))
                ->map(fn ($r) => (string) ($r->tag ?? ''))
                ->filter(fn ($t) => trim($t) !== '')
                ->values();
        } catch (\Throwable) {
            $tags = collect();
        }

        $activity = collect();
        $viewerIsStaff = $this->isStaff();

        foreach ($messages as $m) {
            $mName = trim(($m->user_first_name ?? '').' '.($m->user_last_name ?? ''));
            if ($mName === '') {
                $mName = 'User #'.($m->user_id ?? '');
            }
            $type = (string) ($m->message_type ?? 'public');

            if ($type === 'system') {
                $body = (string) ($m->body ?? '');
                $text = "Update by {$mName}";
                if ($body === 'TICKET_CREATED') {
                    $text = "Ticket created by {$mName}";
                } elseif (str_starts_with($body, 'ASSIGNED_TO:')) {
                    $text = "Ticket assigned by {$mName}";
                } elseif (str_starts_with($body, 'STATUS_CHANGED:')) {
                    $st = trim(substr($body, strlen('STATUS_CHANGED:')));
                    $label = $st;
                    if ($st === 'in_progress') $label = 'In Progress';
                    if ($st === 'open') $label = 'Open';
                    if ($st === 'resolved') $label = 'Resolved';
                    if ($st === 'closed') $label = 'Closed';
                    $text = "Status changed to {$label} by {$mName}";
                } elseif (str_starts_with($body, 'ATTACHMENT_REMOVED:')) {
                    $name = trim(substr($body, strlen('ATTACHMENT_REMOVED:')));
                    $text = "Attachment removed by {$mName} ({$name})";
                } elseif (str_starts_with($body, 'TAG_ADDED:')) {
                    $tag = trim(substr($body, strlen('TAG_ADDED:')));
                    $text = "Tag \"{$tag}\" added by {$mName}";
                } elseif (str_starts_with($body, 'TAG_REMOVED:')) {
                    $tag = trim(substr($body, strlen('TAG_REMOVED:')));
                    $text = "Tag \"{$tag}\" removed by {$mName}";
                } elseif ($body === 'MESSAGE_DELETED') {
                    $text = $viewerIsStaff ? "Message deleted by {$mName}" : 'Message deleted';
                } elseif ($body === 'NOTE_DELETED') {
                    $text = $viewerIsStaff ? "Internal note deleted by {$mName}" : 'Message deleted';
                }

                $activity->push([
                    'at' => $m->created_at ?? null,
                    'text' => $text,
                ]);
                continue;
            }

            $activity->push([
                'at' => $m->created_at ?? null,
                'text' => $type === 'internal' ? "Internal note added by {$mName}" : "Reply posted by {$mName}",
            ]);
        }

        foreach ($files as $f) {
            $uploaderName = trim(($f->uploader_first_name ?? '').' '.($f->uploader_last_name ?? ''));
            if ($uploaderName === '') {
                $uploaderName = 'User #'.($f->uploaded_by ?? '');
            }
            $filename = (string) ($f->original_name ?? basename((string) ($f->stored_path ?? '')));

            $activity->push([
                'at' => $f->created_at ?? null,
                'text' => "Attachment uploaded by {$uploaderName} ({$filename})",
            ]);
        }

        $activity = $activity
            ->filter(fn ($a) => !empty($a['at']))
            ->sortByDesc(fn ($a) => $a['at'])
            ->take(20)
            ->values();

        return view('pages.ticket', ['ticket' => $row, 'messages' => $messages, 'tags' => $tags, 'files' => $files, 'activity' => $activity]);
    }

    public function uploadAttachments(Request $request, int $ticket): RedirectResponse
    {
        $row = $this->getAuthorizedTicketRow($ticket);

        $validated = $request->validate([
            'files' => ['required', 'array', 'min:1'],
            'files.*' => ['file', 'max:10240', 'mimes:png,jpg,jpeg,pdf,doc,docx,txt'],
        ]);

        foreach ($request->file('files', []) as $file) {
            $storedPath = Storage::disk('public')->putFile("tickets/{$ticket}", $file);

            try {
                DB::select(
                    'EXEC dbo.sp_create_ticket_attachment @ticket_id=?, @message_id=?, @uploaded_by=?, @stored_path=?, @original_name=?, @mime=?, @size=?',
                    [
                        $ticket,
                        null,
                        auth()->id(),
                        $storedPath,
                        $file->getClientOriginalName(),
                        $file->getClientMimeType(),
                        $file->getSize(),
                    ]
                );
            } catch (\Throwable) {
                // no-op
            }
        }

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('status', 'Attachments uploaded.');
    }

    public function addTag(Request $request, int $ticket): RedirectResponse
    {
        if (!$this->isStaff()) {
            abort(403);
        }

        $this->getAuthorizedTicketRow($ticket);

        $validated = $request->validate([
            'tag' => ['required', 'string', 'max:50', 'regex:/^[a-zA-Z0-9][a-zA-Z0-9_\- ]*$/'],
        ]);

        $tag = trim($validated['tag']);

        try {
            DB::select('EXEC dbo.sp_create_ticket_tag @ticket_id = ?, @tag = ?', [$ticket, $tag]);

            DB::select(
                'EXEC dbo.sp_create_ticket_message @ticket_id = ?, @user_id = ?, @message_type = ?, @body = ?',
                [$ticket, auth()->id(), 'system', 'TAG_ADDED:'.$tag]
            );
        } catch (\Throwable) {
            // no-op
        }

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('status', 'Tag added.');
    }

    public function removeTag(Request $request, int $ticket): RedirectResponse
    {
        if (!$this->isStaff()) {
            abort(403);
        }

        $this->getAuthorizedTicketRow($ticket);

        $validated = $request->validate([
            'tag' => ['required', 'string', 'max:50'],
        ]);

        $tag = trim($validated['tag']);

        try {
            DB::select('EXEC dbo.sp_delete_ticket_tag @ticket_id = ?, @tag = ?', [$ticket, $tag]);

            DB::select(
                'EXEC dbo.sp_create_ticket_message @ticket_id = ?, @user_id = ?, @message_type = ?, @body = ?',
                [$ticket, auth()->id(), 'system', 'TAG_REMOVED:'.$tag]
            );
        } catch (\Throwable) {
            // no-op
        }

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('status', 'Tag removed.');
    }

    public function storeMessage(Request $request, int $ticket): RedirectResponse
    {
        $this->getAuthorizedTicketRow($ticket);

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
            'message_type' => ['nullable', 'string', 'in:public,internal'],
            'next_status' => ['nullable', 'string', 'in:open,in_progress,resolved,closed'],
            'files' => ['nullable', 'array', 'max:5'],
            'files.*' => ['file', 'max:10240', 'mimes:png,jpg,jpeg,gif,webp,pdf,doc,docx,txt'],
        ]);

        $messageType = $validated['message_type'] ?? 'public';
        if (!$this->isStaff()) {
            $messageType = 'public';
        }

        $created = DB::select(
            'EXEC dbo.sp_create_ticket_message @ticket_id = ?, @user_id = ?, @message_type = ?, @body = ?',
            [$ticket, auth()->id(), $messageType, $validated['body']]
        );

        $messageId = (int) (($created[0]->message_id ?? 0));
        if ($messageId > 0) {
            foreach ($request->file('files', []) as $file) {
                $storedPath = Storage::disk('public')->putFile("tickets/{$ticket}/messages/{$messageId}", $file);

                try {
                    DB::select(
                        'EXEC dbo.sp_create_ticket_attachment @ticket_id=?, @message_id=?, @uploaded_by=?, @stored_path=?, @original_name=?, @mime=?, @size=?',
                        [
                            $ticket,
                            $messageId,
                            auth()->id(),
                            $storedPath,
                            $file->getClientOriginalName(),
                            $file->getClientMimeType(),
                            $file->getSize(),
                        ]
                    );
                } catch (\Throwable) {
                    // no-op
                }
            }
        }

        if ($this->isStaff() && !empty($validated['next_status'])) {
            DB::select(
                'EXEC dbo.sp_update_ticket_status @ticket_id=?, @status=?',
                [$ticket, $validated['next_status']]
            );

            $this->createSystemMessage($ticket, 'STATUS_CHANGED:'.$validated['next_status']);
        }

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('status', 'Message sent.');

    }

    public function viewMessageFile(int $ticket, int $file): BinaryFileResponse
    {
        $this->getAuthorizedTicketRow($ticket);

        $rows = [];
        try {
            $rows = DB::select('EXEC dbo.sp_read_ticket_attachments_by_ticket @ticket_id = ?', [$ticket]);
        } catch (\Throwable) {
            abort(404);
        }

        $match = collect($rows)->firstWhere('file_id', $file);
        if (!$match || !isset($match->message_id) || $match->message_id === null) {
            abort(404);
        }

        return $this->inlineFileResponse(
            (string) ($match->stored_path ?? ''),
            (string) ($match->original_name ?? null),
            isset($match->mime) ? (string) $match->mime : null
        );
    }

    public function viewFile(int $ticket, int $file): BinaryFileResponse
    {
        $this->getAuthorizedTicketRow($ticket);

        $files = collect();
        try {
            $files = collect(DB::select('EXEC dbo.sp_read_ticket_attachments_by_ticket @ticket_id = ?', [$ticket]));
        } catch (\Throwable) {
            abort(404);
        }

        $row = $files->firstWhere('file_id', $file);
        if (!$row || (isset($row->message_id) && $row->message_id !== null)) {
            abort(404);
        }

        return $this->inlineFileResponse(
            (string) ($row->stored_path ?? ''),
            (string) ($row->original_name ?? null),
            isset($row->mime) ? (string) $row->mime : null
        );
    }

    public function viewAttachment(Request $request, int $ticket): BinaryFileResponse
    {
        $row = $this->getAuthorizedTicketRow($ticket);

        $path = (string) $request->query('path', '');
        $path = trim($path);

        if ($path === '' || str_contains($path, '..') || str_starts_with($path, '/') || str_starts_with($path, '\\')) {
            abort(404);
        }

        if (!str_starts_with($path, 'tickets/'.$ticket.'/')) {
            abort(404);
        }

        $allowed = false;

        if (isset($row->attachments) && $row->attachments !== null && $row->attachments !== '') {
            $decoded = json_decode((string) $row->attachments, true);
            if (is_array($decoded) && in_array($path, $decoded, true)) {
                $allowed = true;
            }
        }

        if (!$allowed) {
            try {
                $files = collect(DB::select('EXEC dbo.sp_read_ticket_attachments_by_ticket @ticket_id = ?', [$ticket]));
                $allowed = $files->contains(fn ($f) => (!isset($f->message_id) || $f->message_id === null) && (string) ($f->stored_path ?? '') === $path);
            } catch (\Throwable) {
                $allowed = false;
            }
        }

        if (!$allowed) {
            abort(404);
        }

        return $this->inlineFileResponse($path);
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

        $this->createSystemMessage($ticket, 'ASSIGNED_TO:'.(string) auth()->id());

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

        $this->createSystemMessage($ticket, 'STATUS_CHANGED:'.$validated['status']);

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
            'files' => ['nullable', 'array', 'max:10'],
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

        foreach ($request->file('files', []) as $file) {
            $storedPath = Storage::disk('public')->putFile("tickets/{$ticketId}", $file);

            try {
                DB::select(
                    'EXEC dbo.sp_create_ticket_attachment @ticket_id=?, @message_id=?, @uploaded_by=?, @stored_path=?, @original_name=?, @mime=?, @size=?',
                    [
                        $ticketId,
                        null,
                        auth()->id(),
                        $storedPath,
                        $file->getClientOriginalName(),
                        $file->getClientMimeType(),
                        $file->getSize(),
                    ]
                );
            } catch (\Throwable) {
                // no-op
            }
        }

        $this->createSystemMessage($ticketId, 'TICKET_CREATED');

        return redirect()
            ->route('tickets.index')
            ->with('status', 'Ticket submitted successfully.');
    }

    public function deleteFile(int $ticket, int $file): RedirectResponse
    {
        $this->getAuthorizedTicketRow($ticket);
        $myId = (int) auth()->id();

        $row = null;
        try {
            $files = collect(DB::select('EXEC dbo.sp_read_ticket_attachments_by_ticket @ticket_id = ?', [$ticket]));
            $row = $files->firstWhere('file_id', $file);
        } catch (\Throwable) {
            $row = null;
        }

        if (!$row || (isset($row->message_id) && $row->message_id !== null)) {
            return redirect()->route('tickets.show', $ticket)->with('status', 'Attachment not found.');
        }

        $uploadedBy = (int) ($row->uploaded_by ?? 0);
        if (!$this->isStaff() && $uploadedBy !== $myId) {
            abort(403);
        }

        try {
            DB::statement('EXEC dbo.sp_delete_ticket_attachment @ticket_id = ?, @file_id = ?', [$ticket, $file]);
        } catch (\Throwable) {
            // no-op
        }

        $path = (string) ($row->stored_path ?? '');
        if ($path !== '' && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }

        $this->createSystemMessage($ticket, 'ATTACHMENT_REMOVED:'.((string) ($row->original_name ?? basename($path))));

        return redirect()->route('tickets.show', $ticket)->with('status', 'Attachment removed.');
    }

    public function deleteMessageFile(int $ticket, int $file): RedirectResponse
    {
        $this->getAuthorizedTicketRow($ticket);
        $myId = (int) auth()->id();

        $match = null;
        try {
            $rows = collect(DB::select('EXEC dbo.sp_read_ticket_attachments_by_ticket @ticket_id = ?', [$ticket]));
            $match = $rows->firstWhere('file_id', $file);
        } catch (\Throwable) {
            $match = null;
        }

        if (!$match || !isset($match->message_id) || $match->message_id === null) {
            return redirect()->route('tickets.show', $ticket)->with('status', 'Attachment not found.');
        }

        $uploadedBy = (int) ($match->uploaded_by ?? 0);
        if (!$this->isStaff() && $uploadedBy !== $myId) {
            abort(403);
        }

        try {
            DB::statement('EXEC dbo.sp_delete_ticket_attachment @ticket_id = ?, @file_id = ?', [$ticket, $file]);
        } catch (\Throwable) {
            // no-op
        }

        $path = (string) ($match->stored_path ?? '');
        if ($path !== '' && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }

        $this->createSystemMessage($ticket, 'ATTACHMENT_REMOVED:'.((string) ($match->original_name ?? basename($path))));

        return redirect()->route('tickets.show', $ticket)->with('status', 'Attachment removed.');
    }

    public function deleteLegacyAttachment(Request $request, int $ticket): RedirectResponse
    {
        $row = $this->getAuthorizedTicketRow($ticket);

        if (!$this->isStaff() && (int) ($row->user_id ?? 0) !== (int) auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'path' => ['required', 'string'],
        ]);

        $path = trim((string) $validated['path']);
        if ($path === '' || str_contains($path, '..') || str_starts_with($path, '/') || str_starts_with($path, '\\')) {
            abort(404);
        }
        if (!str_starts_with($path, 'tickets/'.$ticket.'/')) {
            abort(404);
        }

        $attachments = [];
        if (isset($row->attachments) && $row->attachments !== null && $row->attachments !== '') {
            $decoded = json_decode((string) $row->attachments, true);
            if (is_array($decoded)) {
                $attachments = $decoded;
            }
        }

        if (!in_array($path, $attachments, true)) {
            return redirect()->route('tickets.show', $ticket)->with('status', 'Attachment not found.');
        }

        $attachments = array_values(array_filter($attachments, fn ($p) => (string) $p !== $path));

        try {
            DB::statement(
                'EXEC dbo.sp_update_ticket_attachments @ticket_id=?, @attachments=?',
                [$ticket, json_encode($attachments)]
            );
        } catch (\Throwable) {
            // no-op
        }

        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }

        $this->createSystemMessage($ticket, 'ATTACHMENT_REMOVED:'.basename($path));

        return redirect()->route('tickets.show', $ticket)->with('status', 'Attachment removed.');
    }

    public function deleteMessage(int $ticket, int $message): RedirectResponse
    {
        $this->getAuthorizedTicketRow($ticket);

        $myId = (int) auth()->id();
        $rows = DB::select('EXEC dbo.sp_read_ticket_message_by_id @ticket_id = ?, @message_id = ?', [$ticket, $message]);
        $msg = $rows[0] ?? null;

        if (!$msg) {
            abort(404);
        }

        if (((string) ($msg->message_type ?? 'public')) === 'system') {
            abort(403);
        }

        $ownerId = (int) ($msg->user_id ?? 0);
        if (!$this->isStaff() && $ownerId !== $myId) {
            abort(403);
        }

        if (!$this->isStaff() && ((string) ($msg->message_type ?? 'public')) === 'internal') {
            abort(403);
        }

        // Delete any attachments associated with this message.
        try {
            $rows = collect(DB::select('EXEC dbo.sp_read_ticket_attachments_by_ticket @ticket_id = ?', [$ticket]));
            $rows
                ->filter(fn ($f) => isset($f->message_id) && (int) $f->message_id === (int) $message)
                ->each(function ($f) use ($ticket) {
                    $path = (string) ($f->stored_path ?? '');
                    if ($path !== '' && Storage::disk('public')->exists($path)) {
                        Storage::disk('public')->delete($path);
                    }
                    DB::statement('EXEC dbo.sp_delete_ticket_attachment @ticket_id = ?, @file_id = ?', [$ticket, (int) ($f->file_id ?? 0)]);
                });
        } catch (\Throwable) {
            // no-op
        }

        DB::statement('EXEC dbo.sp_delete_ticket_message @ticket_id = ?, @message_id = ?', [$ticket, $message]);

        $deletedType = (string) ($msg->message_type ?? 'public');
        $this->createSystemMessage($ticket, $deletedType === 'internal' ? 'NOTE_DELETED' : 'MESSAGE_DELETED');

        return redirect()->route('tickets.show', $ticket)->with('status', 'Message deleted.');
    }

    public function hardDelete(int $ticket): RedirectResponse
    {
        $role = strtolower((string) (auth()->user()?->role ?? 'user'));
        abort_unless($role === 'admin', 403);

        $row = $this->getAuthorizedTicketRow($ticket);

        // Remove stored files first (best-effort).
        try {
            $rows = collect(DB::select('EXEC dbo.sp_read_ticket_attachments_by_ticket @ticket_id = ?', [$ticket]));
            $rows->each(function ($f) {
                $path = (string) ($f->stored_path ?? '');
                if ($path !== '' && str_starts_with($path, 'tickets/') && Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->delete($path);
                }
            });
        } catch (\Throwable) {
            // no-op
        }

        // Legacy JSON attachments (if any)
        try {
            $raw = (string) ($row->attachments ?? '');
            $decoded = $raw !== '' ? json_decode($raw, true) : null;
            if (is_array($decoded)) {
                foreach ($decoded as $p) {
                    $p = (string) $p;
                    if ($p !== '' && str_starts_with($p, 'tickets/') && Storage::disk('public')->exists($p)) {
                        Storage::disk('public')->delete($p);
                    }
                }
            }
        } catch (\Throwable) {
            // no-op
        }

        DB::statement('EXEC dbo.sp_hard_delete_ticket @ticket_id = ?', [$ticket]);

        return redirect()
            ->route('tickets.index')
            ->with('status', 'Ticket permanently deleted.');
    }
}
