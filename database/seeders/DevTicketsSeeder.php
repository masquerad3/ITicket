<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as FakerFactory;

class DevTicketsSeeder extends Seeder
{
    public function run(): void
    {
        // Avoid accidental seeding in production environments.
        if (!app()->environment(['local', 'testing'])) {
            return;
        }

        $desiredCount = 15;

        // If there are already enough tickets, don't keep piling on.
        try {
            $existingCount = count(DB::select('EXEC dbo.sp_read_all_tickets'));
            $toCreate = max(0, $desiredCount - $existingCount);
        } catch (\Throwable) {
            $toCreate = $desiredCount;
        }

        $faker = FakerFactory::create();

        $admin = $this->findUserIdByEmail('admin@example.com');
        $it = $this->findUserIdByEmail('it@example.com');
        $user = $this->findUserIdByEmail('user@example.com');

        $requesterPool = array_values(array_filter([$user, $admin]));
        if (count($requesterPool) === 0) {
            // DevUsersSeeder wasn't run or users missing.
            return;
        }

        $categories = ['Network', 'Email', 'Hardware', 'Software', 'Account', 'Printer', 'VPN'];
        $departments = ['IT', 'HR', 'Finance', 'Operations', 'Facilities'];
        $locations = ['Main Building', 'Annex', 'Floor 1', 'Floor 2', 'Remote'];
        $priorities = ['High', 'Medium', 'Low'];
        $preferredContacts = ['Email', 'Phone'];

        for ($i = 0; $i < $toCreate; $i++) {
            $requesterId = $requesterPool[array_rand($requesterPool)];

            $category = $categories[array_rand($categories)];
            $priority = $priorities[array_rand($priorities)];
            $department = $departments[array_rand($departments)];
            $location = $locations[array_rand($locations)];
            $preferred = $preferredContacts[array_rand($preferredContacts)];

            // Mix some statuses so the dashboard feels real.
            $statusRoll = random_int(1, 100);
            $status = 'open';
            if ($statusRoll <= 35) {
                $status = 'in_progress';
            } elseif ($statusRoll <= 50) {
                $status = 'resolved';
            }

            $subject = $this->makeSubject($category, $faker);
            $description = $faker->paragraphs(random_int(1, 3), true);

            $rows = DB::select(
                'EXEC dbo.sp_create_ticket @user_id=?, @subject=?, @category=?, @priority=?, @department=?, @location=?, @description=?, @preferred_contact=?, @status=?',
                [
                    $requesterId,
                    $subject,
                    $category,
                    $priority,
                    $department,
                    $location,
                    $description,
                    $preferred,
                    $status,
                ]
            );

            $ticketId = (int) (($rows[0]->ticket_id ?? 0) ?: 0);
            if ($ticketId <= 0) {
                continue;
            }

            // If we created it as in_progress/resolved and we have an IT user, assign it.
            if (in_array($status, ['in_progress', 'resolved'], true) && $it) {
                DB::select('EXEC dbo.sp_assign_ticket_to_user @ticket_id=?, @assigned_to=?', [$ticketId, $it]);
            }

            // Ensure resolved tickets set resolved_at.
            if ($status === 'resolved') {
                DB::select('EXEC dbo.sp_update_ticket_status @ticket_id=?, @status=?', [$ticketId, 'resolved']);
            }

            // Seed a couple messages for the new ticket.
            $this->ensureTicketHasMessages(
                ticketId: $ticketId,
                requesterId: $requesterId,
                assigneeId: $it ?: null,
                status: $status,
                faker: $faker
            );
        }

        // Also backfill messages for recent tickets so the chat view looks populated.
        $this->backfillMessagesForRecentTickets($desiredCount, $it ?: null, $faker);
    }

    private function backfillMessagesForRecentTickets(int $take, ?int $defaultStaffId, $faker): void
    {
        try {
            $tickets = collect(DB::select('EXEC dbo.sp_read_all_tickets'))
                ->take($take)
                ->values();
        } catch (\Throwable) {
            return;
        }

        foreach ($tickets as $t) {
            $ticketId = (int) ($t->ticket_id ?? 0);
            $requesterId = (int) ($t->user_id ?? 0);
            $assigneeId = isset($t->assigned_to) && $t->assigned_to !== null ? (int) $t->assigned_to : ($defaultStaffId ?: null);
            $status = (string) ($t->status ?? 'open');

            if ($ticketId <= 0 || $requesterId <= 0) {
                continue;
            }

            $this->ensureTicketHasMessages(
                ticketId: $ticketId,
                requesterId: $requesterId,
                assigneeId: $assigneeId,
                status: $status,
                faker: $faker
            );
        }
    }

    private function ensureTicketHasMessages(int $ticketId, int $requesterId, ?int $assigneeId, string $status, $faker): void
    {
        try {
            $existing = DB::select(
                'EXEC dbo.sp_read_ticket_messages_by_ticket @ticket_id=?, @include_internal=?',
                [$ticketId, 1]
            );
            $existingCount = count($existing);
        } catch (\Throwable) {
            $existingCount = 0;
        }

        if ($existingCount >= 2) {
            return;
        }

        // 1) Requester opening message
        $this->createMessage(
            ticketId: $ticketId,
            userId: $requesterId,
            type: 'public',
            body: (string) $faker->sentences(random_int(1, 2), true)
        );

        // 2) Staff reply if we have one
        if ($assigneeId) {
            $this->createMessage(
                ticketId: $ticketId,
                userId: $assigneeId,
                type: 'public',
                body: 'Thanks — I’m looking into this now. I’ll update you shortly.'
            );

            // Optional internal note
            if (random_int(1, 100) <= 25) {
                $this->createMessage(
                    ticketId: $ticketId,
                    userId: $assigneeId,
                    type: 'internal',
                    body: 'Internal note: reproducing issue and checking logs/config.'
                );
            }
        }

        // Resolved tickets get a closing message
        if (strtolower(trim($status)) === 'resolved' && $assigneeId) {
            $this->createMessage(
                ticketId: $ticketId,
                userId: $assigneeId,
                type: 'public',
                body: 'This should be resolved now. Please confirm on your side.'
            );
        }
    }

    private function createMessage(int $ticketId, int $userId, string $type, string $body): ?int
    {
        try {
            $rows = DB::select(
                'EXEC dbo.sp_create_ticket_message @ticket_id=?, @user_id=?, @message_type=?, @body=?',
                [$ticketId, $userId, $type, $body]
            );

            return (int) (($rows[0]->message_id ?? 0) ?: 0);
        } catch (\Throwable) {
            return null;
        }
    }

    private function findUserIdByEmail(string $email): ?int
    {
        try {
            $rows = DB::select('EXEC dbo.sp_read_user_by_email @email = ?', [$email]);
            $u = $rows[0] ?? null;
            if (!$u) {
                return null;
            }

            return (int) $u->user_id;
        } catch (\Throwable) {
            return null;
        }
    }

    private function makeSubject(string $category, $faker): string
    {
        $map = [
            'Network' => ['Wi-Fi keeps dropping', 'No internet connection', 'VPN not connecting'],
            'Email' => ['Email not syncing', 'Cannot send emails', 'Mailbox full error'],
            'Hardware' => ['Laptop overheating', 'Keyboard not working', 'Monitor flickering'],
            'Software' => ['App crashes on launch', 'Update failed', 'License activation issue'],
            'Account' => ['Password reset request', 'Account locked', 'Permission issue'],
            'Printer' => ['Printer offline', 'Cannot print PDF', 'Paper jam error'],
            'VPN' => ['VPN timeout', 'VPN credentials rejected', 'VPN client error'],
        ];

        $choices = $map[$category] ?? null;
        if (is_array($choices) && count($choices) > 0) {
            return $choices[array_rand($choices)];
        }

        return (string) $faker->sentence(6);
    }
}
