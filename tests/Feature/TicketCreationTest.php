<?php

namespace Tests\Feature;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TicketCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_ticket(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->post(route('tickets.store'), [
                'subject' => 'Cannot access email',
                'category' => 'Account/Access',
                'priority' => 'High',
                'department' => 'IT',
                'location' => 'Main 3A',
                'description' => str_repeat('A', 50),
                'contact' => 'email',
                'consent' => '1',
                'files' => [
                    UploadedFile::fake()->image('screenshot.jpg'),
                ],
            ]);

        $response->assertRedirect(route('tickets'));

        $this->assertSame(1, Ticket::count());

        $ticket = Ticket::first();
        $this->assertNotNull($ticket);
        $this->assertSame('Cannot access email', $ticket->subject);
        $this->assertSame($user->user_id, $ticket->user_id);

        $this->assertIsArray($ticket->attachments);
        $this->assertCount(1, $ticket->attachments);

        Storage::disk('public')->assertExists($ticket->attachments[0]);
    }
}
