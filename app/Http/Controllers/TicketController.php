<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TicketController extends Controller
{
    private function isStaff(): bool
    {
        $role = strtolower((string) (auth()->user()?->role ?? 'user'));

        return in_array($role, ['it', 'admin'], true);
    }

    public function index()
    {
        $query = Ticket::query()->with(['assignee', 'requester']);

        if (!$this->isStaff()) {
            $query->where('user_id', auth()->id());
        }

        $tickets = $query
            ->latest('created_at')
            ->get();

        $counts = [
            'total' => $tickets->count(),
            'open' => $tickets->where('status', 'open')->count(),
            'progress' => $tickets->where('status', 'in_progress')->count(),
            'resolved' => $tickets->where('status', 'resolved')->count(),
        ];

        return view('pages.tickets', compact('tickets', 'counts'));
    }

    public function create()
    {
        return view('pages.create-ticket');
    }

    public function show(Ticket $ticket)
    {
        if (!$this->isStaff() && (int) $ticket->user_id !== (int) auth()->id()) {
            abort(404);
        }

        $ticket->load(['assignee', 'requester']);

        return view('pages.ticket', compact('ticket'));
    }

    public function assignToMe(Ticket $ticket): RedirectResponse
    {
        if (!$this->isStaff()) {
            abort(403);
        }

        if ($ticket->assigned_to === null) {
            $ticket->assigned_to = auth()->id();
            $ticket->assigned_at = now();
        }

        if ($ticket->status === 'open') {
            $ticket->status = 'in_progress';
        }

        $ticket->save();

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('status', 'Ticket assigned successfully.');
    }

    public function updateStatus(Request $request, Ticket $ticket): RedirectResponse
    {
        if (!$this->isStaff()) {
            abort(403);
        }

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:open,in_progress,resolved,closed'],
        ]);

        $ticket->status = $validated['status'];

        if ($ticket->status === 'resolved') {
            $ticket->resolved_at = now();
        }

        if ($ticket->status !== 'resolved') {
            $ticket->resolved_at = null;
        }

        $ticket->save();

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

        $ticket = Ticket::create([
            'user_id' => auth()->id(),
            'subject' => $validated['subject'],
            'category' => $validated['category'],
            'priority' => $validated['priority'],
            'department' => $validated['department'] ?? null,
            'location' => $validated['location'] ?? null,
            'description' => $validated['description'],
            'preferred_contact' => $validated['contact'],
            'status' => 'open',
        ]);

        $storedPaths = [];
        foreach ($request->file('files', []) as $file) {
            $storedPaths[] = Storage::disk('public')->putFile("tickets/{$ticket->ticket_id}", $file);
        }

        if (!empty($storedPaths)) {
            $ticket->attachments = $storedPaths;
            $ticket->save();
        }

        return redirect()
            ->route('tickets.index')
            ->with('status', 'Ticket submitted successfully.');
    }
}
