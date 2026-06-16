<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Database\Seeders\RoleSeeder;

class MailboxTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);

        $employeeRole = Role::where('slug', 'employee')->first();

        $this->user = User::factory()->create([
            'role_id' => $employeeRole->id,
            'name' => 'Alice User',
            'mailbox_imap_enabled' => true,
            'mailbox_imap_host' => 'imap.test.com',
            'mailbox_imap_port' => '993',
            'mailbox_imap_encryption' => 'ssl',
            'mailbox_imap_username' => 'alice@test.com',
            'mailbox_imap_password' => 'secret_imap_pass',
            'mailbox_smtp_host' => 'smtp.test.com',
            'mailbox_smtp_port' => '465',
            'mailbox_smtp_encryption' => 'ssl',
            'mailbox_smtp_username' => 'alice@test.com',
            'mailbox_smtp_password' => 'secret_smtp_pass',
        ]);
    }

    public function test_guest_cannot_access_mailbox()
    {
        $this->get(route('mailbox.index'))
            ->assertRedirect(route('login'));
    }

    public function test_user_can_access_mailbox_index()
    {
        $response = $this->actingAs($this->user)
            ->get(route('mailbox.index'));

        $response->assertStatus(200);
        $response->assertSee('Compose');
        $response->assertSee('Inbox');
    }

    public function test_user_can_send_mail()
    {
        Mail::fake();

        $response = $this->actingAs($this->user)
            ->post(route('mailbox.store'), [
                'to_email' => 'receiver@example.com',
                'subject' => 'Dynamic Domain Subject',
                'body' => 'This is the dynamic domain body text.',
            ]);

        $response->assertStatus(200);
        $response->assertJsonFragment(['success' => true]);

        Mail::assertSent(\App\Mail\PersonalDomainMail::class, function ($mail) {
            return $mail->hasTo('receiver@example.com') && $mail->subject === 'Dynamic Domain Subject';
        });
    }

    public function test_guest_cannot_access_official_endpoints()
    {
        $this->get(route('mailbox.official.index'))
            ->assertRedirect(route('login'));

        $this->get(route('mailbox.official.show', ['uid' => 1]))
            ->assertRedirect(route('login'));
    }

    public function test_official_index_when_disabled_returns_local_messages()
    {
        $this->user->update(['mailbox_imap_enabled' => false]);

        $sender = User::factory()->create();
        \App\Models\MailboxMessage::create([
            'sender_id' => $sender->id,
            'receiver_id' => $this->user->id,
            'subject' => 'Local Subject',
            'body' => 'Local Body',
            'is_read' => false,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('mailbox.official.index'));

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'success' => true,
        ]);
        $this->assertCount(1, $response->json('messages'));
        $this->assertEquals('Local Subject', $response->json('messages.0.subject'));
    }

    public function test_user_can_save_personal_mailbox_settings()
    {
        $response = $this->actingAs($this->user)
            ->post(route('mailbox.settings.save'), [
                'mailbox_imap_enabled' => 1,
                'mailbox_imap_host' => 'imap.new.com',
                'mailbox_imap_port' => '993',
                'mailbox_imap_encryption' => 'ssl',
                'mailbox_imap_username' => 'new@new.com',
                'mailbox_imap_password' => 'new_imap_password',
                'mailbox_smtp_host' => 'smtp.new.com',
                'mailbox_smtp_port' => '587',
                'mailbox_smtp_encryption' => 'tls',
                'mailbox_smtp_username' => 'new@new.com',
                'mailbox_smtp_password' => 'new_smtp_password',
            ]);

        $response->assertStatus(200);
        $response->assertJsonFragment(['success' => true]);

        $this->user->refresh();
        $this->assertTrue($this->user->mailbox_imap_enabled);
        $this->assertEquals('imap.new.com', $this->user->mailbox_imap_host);
        $this->assertEquals('new_imap_password', $this->user->mailbox_imap_password);
        $this->assertEquals('smtp.new.com', $this->user->mailbox_smtp_host);
        $this->assertEquals('new_smtp_password', $this->user->mailbox_smtp_password);
    }

    public function test_employee_cannot_access_other_user_mailbox()
    {
        $otherUser = User::factory()->create([
            'role_id' => Role::where('slug', 'employee')->first()->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('mailbox.index', ['user_id' => $otherUser->id]));

        $response->assertStatus(403);
    }

    public function test_admin_can_access_other_user_mailbox()
    {
        $adminUser = User::factory()->create([
            'role_id' => Role::where('slug', 'admin')->first()->id,
        ]);

        $response = $this->actingAs($adminUser)
            ->get(route('mailbox.index', ['user_id' => $this->user->id]));

        $response->assertStatus(200);
    }

    public function test_hr_can_access_other_user_mailbox()
    {
        $hrUser = User::factory()->create([
            'role_id' => Role::where('slug', 'hr')->first()->id,
        ]);

        $response = $this->actingAs($hrUser)
            ->get(route('mailbox.index', ['user_id' => $this->user->id]));

        $response->assertStatus(200);
    }
}
