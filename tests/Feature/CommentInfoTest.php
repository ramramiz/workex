<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Task;
use App\Models\Role;
use App\Models\TaskComment;
use App\Models\TaskCommentView;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\RoleSeeder;

class CommentInfoTest extends TestCase
{
    use RefreshDatabase;

    public function test_viewing_task_records_comment_views_and_shows_them_in_modal_payload()
    {
        $this->seed(RoleSeeder::class);
        $employeeRole = Role::where('slug', 'employee')->first();

        $sender = User::factory()->create(['role_id' => $employeeRole->id, 'name' => 'Alice Sender']);
        $viewer = User::factory()->create(['role_id' => $employeeRole->id, 'name' => 'Bob Viewer']);

        $task = Task::create([
            'title' => 'Test Task',
            'assigned_to' => $sender->id,
            'created_by' => $sender->id,
            'status' => 'pending',
        ]);

        $comment = TaskComment::create([
            'task_id' => $task->id,
            'user_id' => $sender->id,
            'comment' => 'This is a message from Alice',
        ]);

        // Prior to viewing, there should be no views recorded for this comment
        $this->assertEquals(0, TaskCommentView::where('task_comment_id', $comment->id)->count());

        // Now, viewer accesses the task details page
        $response = $this->actingAs($viewer)
            ->get(route('tasks.show', $task));

        $response->assertStatus(200);

        // Assert that a view record is successfully created for Bob Viewer
        $this->assertDatabaseHas('task_comment_views', [
            'task_comment_id' => $comment->id,
            'user_id' => $viewer->id,
        ]);

        // Access task details page as the sender (Alice), and check if Bob is in the viewers list payload
        $response2 = $this->actingAs($sender)
            ->get(route('tasks.show', $task));

        $response2->assertStatus(200);
        $response2->assertSee('Bob Viewer');
        $response2->assertSee('data-viewers');
    }

    public function test_comment_with_base64_image_data_is_stored_successfully()
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        $this->seed(RoleSeeder::class);
        $employeeRole = Role::where('slug', 'employee')->first();
        $user = User::factory()->create(['role_id' => $employeeRole->id]);

        $task = Task::create([
            'title' => 'Test Task',
            'assigned_to' => $user->id,
            'created_by' => $user->id,
            'status' => 'pending',
        ]);

        $base64Image = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==';

        $response = $this->actingAs($user)
            ->post(route('tasks.comments.store', $task), [
                'comment' => 'Check this annotated image',
                'image_data' => $base64Image,
            ]);

        $response->assertRedirect();

        $comment = TaskComment::where('task_id', $task->id)->first();
        $this->assertNotNull($comment);
        $this->assertEquals('Check this annotated image', $comment->comment);
        $this->assertNotNull($comment->image_path);
        
        \Illuminate\Support\Facades\Storage::disk('public')->assertExists($comment->image_path);
    }
}
