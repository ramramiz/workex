<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Company;
use App\Models\DirectMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SettingsSeeder;

class DirectMessageActionsTest extends TestCase
{
    use RefreshDatabase;

    protected User $userA;
    protected User $userB;
    protected Company $company;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
        $this->seed(SettingsSeeder::class);

        $this->company = Company::create([
            'name' => 'Test Tech Company',
            'status' => 'active',
        ]);

        $employeeRole = Role::where('slug', 'employee')->first();

        $this->userA = User::factory()->create([
            'name' => 'User Alice',
            'email' => 'alice@testcompany.com',
            'role_id' => $employeeRole->id,
            'company_id' => $this->company->id,
        ]);

        $this->userB = User::factory()->create([
            'name' => 'User Bob',
            'email' => 'bob@testcompany.com',
            'role_id' => $employeeRole->id,
            'company_id' => $this->company->id,
        ]);
    }

    public function test_user_can_edit_own_message_within_30_minutes()
    {
        $message = DirectMessage::create([
            'sender_id' => $this->userA->id,
            'receiver_id' => $this->userB->id,
            'message' => 'Original Message Content',
            'company_id' => $this->company->id,
            'created_at' => now()->subMinutes(10),
        ]);

        $response = $this->actingAs($this->userA)
            ->post(route('direct-chat.messages.edit', $message), [
                'message' => 'Edited Message Content',
            ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('message', 'Edited Message Content');

        $this->assertDatabaseHas('direct_messages', [
            'id' => $message->id,
            'message' => 'Edited Message Content',
            'is_edited' => true,
        ]);
    }

    public function test_user_cannot_edit_own_message_after_30_minutes()
    {
        $message = DirectMessage::create([
            'sender_id' => $this->userA->id,
            'receiver_id' => $this->userB->id,
            'message' => 'Original Message Content',
            'company_id' => $this->company->id,
        ]);
        $message->created_at = now()->subMinutes(35);
        $message->save();

        $response = $this->actingAs($this->userA)
            ->post(route('direct-chat.messages.edit', $message), [
                'message' => 'Edited Message Content',
            ]);

        $response->assertStatus(400);

        $this->assertDatabaseHas('direct_messages', [
            'id' => $message->id,
            'message' => 'Original Message Content',
            'is_edited' => false,
        ]);
    }

    public function test_user_cannot_edit_others_message()
    {
        $message = DirectMessage::create([
            'sender_id' => $this->userB->id,
            'receiver_id' => $this->userA->id,
            'message' => 'Bobs original Message',
            'company_id' => $this->company->id,
            'created_at' => now()->subMinutes(10),
        ]);

        $response = $this->actingAs($this->userA)
            ->post(route('direct-chat.messages.edit', $message), [
                'message' => 'Alice tries to edit Bobs message',
            ]);

        $response->assertStatus(403);
    }

    public function test_user_can_toggle_message_pin_and_star()
    {
        $message = DirectMessage::create([
            'sender_id' => $this->userA->id,
            'receiver_id' => $this->userB->id,
            'message' => 'Important Message to pin or star',
            'company_id' => $this->company->id,
        ]);

        // Pin message
        $response = $this->actingAs($this->userA)
            ->post(route('direct-chat.messages.toggle-pin', $message));
        $response->assertStatus(200);
        $response->assertJsonPath('is_pinned', true);
        $this->assertTrue($message->fresh()->is_pinned);

        // Unpin message
        $response = $this->actingAs($this->userA)
            ->post(route('direct-chat.messages.toggle-pin', $message));
        $response->assertStatus(200);
        $response->assertJsonPath('is_pinned', false);
        $this->assertFalse($message->fresh()->is_pinned);

        // Star message
        $response = $this->actingAs($this->userA)
            ->post(route('direct-chat.messages.toggle-important', $message));
        $response->assertStatus(200);
        $response->assertJsonPath('is_important', true);
        $this->assertTrue($message->fresh()->is_important);
    }

    public function test_conversation_history_includes_pinned_and_star_status_and_seen_info()
    {
        // Alice sends message, Bob reads it
        $message = DirectMessage::create([
            'sender_id' => $this->userA->id,
            'receiver_id' => $this->userB->id,
            'message' => 'Message to Bob',
            'company_id' => $this->company->id,
            'read_at' => now(),
            'is_pinned' => true,
            'is_important' => true,
            'is_edited' => true,
        ]);

        $response = $this->actingAs($this->userA)
            ->get(route('direct-chat.show', $this->userB));

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonCount(1, 'messages');
        
        $msgJson = $response->json('messages.0');
        $this->assertTrue($msgJson['is_pinned']);
        $this->assertTrue($msgJson['is_important']);
        $this->assertTrue($msgJson['is_edited']);
        $this->assertCount(1, $msgJson['seen_by']);
        $this->assertEquals('User Bob', $msgJson['seen_by'][0]['name']);
    }
}
