<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TicketController extends Controller
{
    public function index()
    {
        $tickets = Ticket::query()
            ->where('user_id', auth()->id())
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
            ->route('tickets')
            ->with('status', 'Ticket submitted successfully.');
    }
}
