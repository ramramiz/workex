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
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class DirectChatTest extends TestCase
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

        // Create a test company
        $this->company = Company::create([
            'name' => 'Test Tech Company',
            'status' => 'active',
        ]);

        $employeeRole = Role::where('slug', 'employee')->first();

        // Create User A
        $this->userA = User::factory()->create([
            'name' => 'User Alice',
            'email' => 'alice@testcompany.com',
            'role_id' => $employeeRole->id,
            'company_id' => $this->company->id,
        ]);

        // Create User B
        $this->userB = User::factory()->create([
            'name' => 'User Bob',
            'email' => 'bob@testcompany.com',
            'role_id' => $employeeRole->id,
            'company_id' => $this->company->id,
        ]);
    }

    public function test_user_can_access_direct_chat_dashboard()
    {
        $response = $this->actingAs($this->userA)
            ->get(route('direct-chat.index'));

        $response->assertStatus(200);
        $response->assertSee('User Bob');
    }

    public function test_user_can_send_direct_text_message()
    {
        $response = $this->actingAs($this->userA)
            ->post(route('direct-chat.send', $this->userB), [
                'message' => 'Hello Bob! This is Alice.',
            ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('message.message', 'Hello Bob! This is Alice.');

        $this->assertDatabaseHas('direct_messages', [
            'sender_id' => $this->userA->id,
            'receiver_id' => $this->userB->id,
            'message' => 'Hello Bob! This is Alice.',
            'company_id' => $this->company->id,
        ]);
    }

    public function test_user_can_send_direct_message_with_uploaded_image()
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('photo.jpg');

        $response = $this->actingAs($this->userA)
            ->post(route('direct-chat.send', $this->userB), [
                'message' => 'Look at this photo',
                'image' => $file,
            ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        
        $message = DirectMessage::first();
        $this->assertNotNull($message->image_path);
        Storage::disk('public')->assertExists($message->image_path);
    }

    public function test_user_can_send_direct_message_with_base64_image()
    {
        Storage::fake('public');

        // Simple base64 image data
        $base64Image = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

        $response = $this->actingAs($this->userA)
            ->post(route('direct-chat.send', $this->userB), [
                'message' => 'Base64 image upload',
                'image_data' => $base64Image,
            ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);

        $message = DirectMessage::first();
        $this->assertNotNull($message->image_path);
        Storage::disk('public')->assertExists($message->image_path);
    }

    public function test_user_can_load_conversation_history_and_mark_as_read()
    {
        // Alice sends Bob a message
        $msg = DirectMessage::create([
            'sender_id' => $this->userA->id,
            'receiver_id' => $this->userB->id,
            'message' => 'Secret message',
            'company_id' => $this->company->id,
        ]);

        $this->assertNull($msg->read_at);

        // Bob opens Alice's chat thread
        $response = $this->actingAs($this->userB)
            ->get(route('direct-chat.show', $this->userA));

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonCount(1, 'messages');
        $response->assertJsonPath('messages.0.message', 'Secret message');

        // Verify Bob's read status is updated
        $this->assertNotNull($msg->fresh()->read_at);
    }

    public function test_user_can_poll_new_messages_and_unread_counts()
    {
        // Alice sends Bob a message
        DirectMessage::create([
            'sender_id' => $this->userA->id,
            'receiver_id' => $this->userB->id,
            'message' => 'New update',
            'company_id' => $this->company->id,
        ]);

        // Bob polls for updates
        $response = $this->actingAs($this->userB)
            ->get(route('direct-chat.updates'));

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonCount(1, 'new_messages');
        $response->assertJsonPath('new_messages.0.message', 'New update');
        $response->assertJsonPath('total_unread', 1);
        $response->assertJsonPath('unread_counts.' . $this->userA->id, 1);
    }

    public function test_direct_messages_are_isolated_by_company()
    {
        // Create another company
        $otherCompany = Company::create([
            'name' => 'Other Company Ltd',
            'status' => 'active',
        ]);

        $employeeRole = Role::where('slug', 'employee')->first();

        // Create User C in the other company
        $userC = User::factory()->create([
            'name' => 'User Charlie',
            'email' => 'charlie@othercompany.com',
            'role_id' => $employeeRole->id,
            'company_id' => $otherCompany->id,
        ]);

        // Alice (User A) accesses dashboard, should NOT see Charlie (User C)
        $response = $this->actingAs($this->userA)
            ->get(route('direct-chat.index'));

        $response->assertStatus(200);
        $response->assertDontSee('User Charlie');

        // Verify that Charlie cannot see Alice or Bob either
        $response2 = $this->actingAs($userC)
            ->get(route('direct-chat.index'));

        $response2->assertStatus(200);
        $response2->assertDontSee('User Alice');
        $response2->assertDontSee('User Bob');
    }

    public function test_user_can_send_direct_message_with_pdf_document()
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->create('report.pdf', 500, 'application/pdf');

        $response = $this->actingAs($this->userA)
            ->post(route('direct-chat.send', $this->userB), [
                'message' => 'Check this report',
                'document' => $file,
            ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        
        $message = DirectMessage::first();
        $this->assertNotNull($message->file_path);
        $this->assertEquals('report.pdf', $message->file_name);
        Storage::disk('public')->assertExists($message->file_path);
    }

    public function test_user_cannot_send_invalid_document_type()
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->create('document.docx', 500, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');

        $responseJson = $this->actingAs($this->userA)
            ->postJson(route('direct-chat.send', $this->userB), [
                'document' => $file,
            ]);
        $responseJson->assertStatus(422);
        $responseJson->assertJsonValidationErrors('document');
    }

    public function test_user_cannot_send_oversized_document()
    {
        Storage::fake('public');

        // 21 MB (21500 KB) is larger than the max limit of 20MB (20480 KB)
        $file = UploadedFile::fake()->create('huge.pdf', 21500, 'application/pdf');

        $response = $this->actingAs($this->userA)
            ->postJson(route('direct-chat.send', $this->userB), [
                'document' => $file,
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('document');
    }
}
