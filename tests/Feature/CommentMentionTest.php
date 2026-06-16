<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Task;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\RoleSeeder;

class CommentMentionTest extends TestCase
{
    use RefreshDatabase;

    public function test_comment_mention_sends_notification()
    {
        $this->seed(RoleSeeder::class);
        $employeeRole = Role::where('slug', 'employee')->first();

        $sender = User::factory()->create(['role_id' => $employeeRole->id, 'name' => 'Alice Sender']);
        $receiver = User::factory()->create(['role_id' => $employeeRole->id, 'name' => 'Bob Receiver']);

        $task = Task::create([
            'title' => 'Test Task',
            'assigned_to' => $sender->id,
            'created_by' => $sender->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($sender)
            ->post(route('tasks.comments.store', $task), [
                'comment' => 'Hey @Bob Receiver please look at this task'
            ]);

        $response->assertRedirect();

        // Assert a comment was created
        $this->assertDatabaseHas('task_comments', [
            'task_id' => $task->id,
            'user_id' => $sender->id,
            'comment' => 'Hey @Bob Receiver please look at this task',
        ]);

        // Assert notification was sent to receiver
        $this->assertDatabaseHas('notifications', [
            'user_id' => $receiver->id,
            'type' => 'mention',
            'title' => 'You were mentioned',
        ]);
    }
}
