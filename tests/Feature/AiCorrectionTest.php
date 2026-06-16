<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\RoleSeeder;

class AiCorrectionTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
        $role = Role::where('slug', 'employee')->first();
        $this->user = User::factory()->create([
            'role_id' => $role->id,
        ]);
    }

    public function test_unauthenticated_user_cannot_correct_text()
    {
        $response = $this->postJson(route('ai.correct'), [
            'text' => 'test sentance',
        ]);

        $response->assertStatus(401);
    }

    public function test_can_correct_user_input_grammar_and_typos()
    {
        $response = $this->actingAs($this->user)
            ->postJson(route('ai.correct'), [
                'text' => 'this is a test sentance and teh code has wont crash.',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'corrected' => 'This is a test sentence and the code has won\'t crash.',
            ]);
    }

    public function test_handles_exact_predefined_mappings()
    {
        $response = $this->actingAs($this->user)
            ->postJson(route('ai.correct'), [
                'text' => 'option upload section image or pdf',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'corrected' => 'Option to upload an image or PDF.',
            ]);
    }
}
