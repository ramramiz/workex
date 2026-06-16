<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use App\Models\UserEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserEmailsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Create standard roles
        $adminRole = Role::create([
            'name' => 'Super Admin',
            'slug' => 'super-admin',
            'description' => 'Full access',
            'color' => '#dc2626'
        ]);

        $this->admin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role_id' => $adminRole->id,
            'status' => 'active'
        ]);

        $this->user = User::create([
            'name' => 'Test User',
            'email' => 'user@example.com',
            'password' => bcrypt('password'),
            'role_id' => $adminRole->id,
            'status' => 'active'
        ]);
    }

    public function test_admin_can_view_user_emails_page(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('users.emails.index', $this->user));

        $response->assertStatus(200);
        $response->assertSee('Email Accounts for ' . $this->user->name);
    }

    public function test_admin_can_add_secondary_email(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('users.emails.store', $this->user), [
                'email' => 'sec1@example.com'
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('user_emails', [
            'user_id' => $this->user->id,
            'email' => 'sec1@example.com'
        ]);
    }

    public function test_cannot_add_invalid_email(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('users.emails.store', $this->user), [
                'email' => 'not-an-email'
            ]);

        $response->assertSessionHasErrors('email');
        $this->assertDatabaseCount('user_emails', 0);
    }

    public function test_cannot_add_duplicate_email(): void
    {
        // Add one email
        UserEmail::create([
            'user_id' => $this->user->id,
            'email' => 'sec1@example.com'
        ]);

        // Try adding the same email
        $response = $this->actingAs($this->admin)
            ->post(route('users.emails.store', $this->user), [
                'email' => 'sec1@example.com'
            ]);

        $response->assertSessionHasErrors('email');
        
        // Try adding primary email of another user
        $response2 = $this->actingAs($this->admin)
            ->post(route('users.emails.store', $this->user), [
                'email' => 'admin@example.com'
            ]);

        $response2->assertSessionHasErrors('email');
    }

    public function test_admin_can_delete_secondary_email(): void
    {
        $email = UserEmail::create([
            'user_id' => $this->user->id,
            'email' => 'sec1@example.com'
        ]);

        $response = $this->actingAs($this->admin)
            ->delete(route('users.emails.destroy', [$this->user, $email]));

        $response->assertRedirect();
        $this->assertDatabaseMissing('user_emails', [
            'id' => $email->id
        ]);
    }
}
