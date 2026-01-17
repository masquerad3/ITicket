<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    private function isStaff(): bool
    {
        $role = strtolower((string) (auth()->user()?->role ?? 'user'));

        return in_array($role, ['it', 'admin'], true);
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

    private function normalizeTicketCollection($rows)
    {
        return collect($rows)
            ->map(fn ($t) => $this->normalizeTicketTimestamps($t))
            ->values();
    }

    private function normalizeStatus(?string $status): string
    {
        $s = strtolower(trim((string) $status));

        return match ($s) {
            'open' => 'open',
            'in progress', 'in_progress', 'progress' => 'in_progress',
            'resolved', 'closed' => 'resolved',
            default => $s === '' ? 'open' : $s,
        };
    }

    private function computeStatusCounts($tickets): array
    {
        $tickets = collect($tickets);

        return [
            'total' => $tickets->count(),
            'open' => $tickets->filter(fn ($t) => $this->normalizeStatus($t->status ?? null) === 'open')->count(),
            'progress' => $tickets->filter(fn ($t) => $this->normalizeStatus($t->status ?? null) === 'in_progress')->count(),
            'resolved' => $tickets->filter(fn ($t) => $this->normalizeStatus($t->status ?? null) === 'resolved')->count(),
        ];
    }

    private function normalizePriority(?string $priority): string
    {
        $p = strtolower(trim((string) $priority));

        return match ($p) {
            'high' => 'high',
            'low' => 'low',
            'medium', 'med' => 'medium',
            default => $p === '' ? 'medium' : $p,
        };
    }

    private function computePriorityCounts($tickets): array
    {
        $tickets = collect($tickets);

        return [
            'high' => $tickets->filter(fn ($t) => $this->normalizePriority($t->priority ?? null) === 'high')->count(),
            'medium' => $tickets->filter(fn ($t) => $this->normalizePriority($t->priority ?? null) === 'medium')->count(),
            'low' => $tickets->filter(fn ($t) => $this->normalizePriority($t->priority ?? null) === 'low')->count(),
        ];
    }

    private function computeAgingCounts($tickets): array
    {
        $tickets = collect($tickets);
        $now = now();
        $cutoff24h = $now->copy()->subHours(24);
        $cutoff3d = $now->copy()->subDays(3);

        $openTickets = $tickets->filter(fn ($t) => $this->normalizeStatus($t->status ?? null) !== 'resolved');

        $olderThan24h = $openTickets->filter(function ($t) use ($cutoff24h) {
            $created = $t->created_at ?? null;
            return $created instanceof Carbon && $created->lessThanOrEqualTo($cutoff24h);
        })->count();

        $olderThan3d = $openTickets->filter(function ($t) use ($cutoff3d) {
            $created = $t->created_at ?? null;
            return $created instanceof Carbon && $created->lessThanOrEqualTo($cutoff3d);
        })->count();

        return [
            'open_older_24h' => $olderThan24h,
            'open_older_3d' => $olderThan3d,
        ];
    }

    private function computeTopCategories($tickets, int $take = 3)
    {
        $tickets = collect($tickets);

        return $tickets
            ->map(function ($t) {
                $c = trim((string) ($t->category ?? ''));
                return $c === '' ? null : $c;
            })
            ->filter()
            ->countBy()
            ->sortDesc()
            ->take($take);
    }

    private function pickRecent($tickets, int $take = 5)
    {
        return collect($tickets)
            ->sortByDesc(function ($t) {
                $created = $t->created_at ?? null;
                return $created instanceof Carbon ? $created->timestamp : 0;
            })
            ->take($take)
            ->values();
    }

    public function show(Request $request)
    {
        $isStaff = $this->isStaff();

        $overviewTickets = collect();
        $unassignedTickets = collect();
        $assignedToMeTickets = collect();

        if (!$isStaff) {
            $overviewTickets = $this->normalizeTicketCollection(
                DB::select('EXEC dbo.sp_read_tickets_by_user @user_id = ?', [auth()->id()])
            );
        } else {
            try {
                // Staff gets a global overview + workload breakdown
                $overviewTickets = $this->normalizeTicketCollection(DB::select('EXEC dbo.sp_read_all_tickets'));
                $unassignedTickets = $this->normalizeTicketCollection(DB::select('EXEC dbo.sp_read_unassigned_tickets'));
                $assignedToMeTickets = $this->normalizeTicketCollection(
                    DB::select('EXEC dbo.sp_read_tickets_assigned_to_user @assigned_to = ?', [auth()->id()])
                );
            } catch (\Throwable) {
                $overviewTickets = $this->normalizeTicketCollection(DB::select('EXEC dbo.sp_read_all_tickets'));
            }
        }

        $counts = $this->computeStatusCounts($overviewTickets);
        $recentTickets = $this->pickRecent($overviewTickets, 5);
        $aging = $this->computeAgingCounts($overviewTickets);
        $priorityBreakdown = $this->computePriorityCounts($overviewTickets);
        $topCategories = $this->computeTopCategories($overviewTickets, 3);

        return view('pages.dashboard', [
            'user' => auth()->user(),
            'is_staff' => $isStaff,
            'counts' => $counts,
            'recentTickets' => $recentTickets,
            'unassignedTickets' => $unassignedTickets,
            'assignedToMeTickets' => $assignedToMeTickets,
            'aging' => $aging,
            'priorityBreakdown' => $priorityBreakdown,
            'topCategories' => $topCategories,
        ]);
    }
}
