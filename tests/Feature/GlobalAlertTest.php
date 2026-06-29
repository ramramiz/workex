<?php

namespace Tests\Feature;

use App\Models\AppAlert;
use App\Models\AppAlertUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GlobalAlertTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $telecaller;

    protected function setUp(): void
    {
        parent::setUp();

        $superAdminRole = \App\Models\Role::firstOrCreate(
            ['slug' => 'super-admin'],
            ['name' => 'Super Admin']
        );

        $telecallerRole = \App\Models\Role::firstOrCreate(
            ['slug' => 'telecaller'],
            ['name' => 'Telecaller']
        );

        $this->superAdmin = User::factory()->create([
            'role_id' => $superAdminRole->id,
            'name' => 'Super Admin'
        ]);

        $this->telecaller = User::factory()->create([
            'role_id' => $telecallerRole->id,
            'name' => 'Telecaller One'
        ]);
    }

    public function test_super_admin_can_send_global_alert_to_all_users(): void
    {
        $response = $this->actingAs($this->superAdmin)->post(route('admin.alerts.store'), [
            'heading' => 'URGENT SYSTEM UPDATE',
            'title' => 'Please clear cache and log in again.',
            'target' => 'all'
        ]);

        $response->assertRedirect(route('admin.alerts.index'));
        $this->assertDatabaseHas('app_alerts', [
            'heading' => 'URGENT SYSTEM UPDATE',
            'title' => 'Please clear cache and log in again.',
        ]);

        // Assert the alert user record was created for the telecaller but not the super-admin creator
        $this->assertDatabaseHas('app_alert_users', [
            'user_id' => $this->telecaller->id,
            'confirmed_at' => null
        ]);
        $this->assertDatabaseMissing('app_alert_users', [
            'user_id' => $this->superAdmin->id
        ]);
    }

    public function test_super_admin_can_send_global_alert_to_selected_users(): void
    {
        $employeeRole = \App\Models\Role::firstOrCreate(
            ['slug' => 'employee'],
            ['name' => 'Employee']
        );
        $otherUser = User::factory()->create(['role_id' => $employeeRole->id]);

        $response = $this->actingAs($this->superAdmin)->post(route('admin.alerts.store'), [
            'heading' => 'INDIVIDUAL NOTICE',
            'title' => 'Notice for selected users.',
            'target' => 'selected',
            'users' => [$this->telecaller->id]
        ]);

        $response->assertRedirect(route('admin.alerts.index'));

        // Alert user record should exist for telecaller but not the other user
        $this->assertDatabaseHas('app_alert_users', [
            'user_id' => $this->telecaller->id
        ]);
        $this->assertDatabaseMissing('app_alert_users', [
            'user_id' => $otherUser->id
        ]);
    }

    public function test_alert_overlay_is_displayed_for_user_with_pending_alert(): void
    {
        $alert = AppAlert::create([
            'heading' => 'URGENT SYSTEM WARNING',
            'title' => 'System will go offline in 5 minutes.',
            'created_by' => $this->superAdmin->id
        ]);

        AppAlertUser::create([
            'app_alert_id' => $alert->id,
            'user_id' => $this->telecaller->id,
            'confirmed_at' => null
        ]);

        $response = $this->actingAs($this->telecaller)->get(route('dashboard'));
        $response->assertStatus(200);

        // Verify overlay elements are visible
        $response->assertSee('global-alert-overlay');
        $response->assertSee('URGENT SYSTEM WARNING');
        $response->assertSee('System will go offline in 5 minutes.');
    }

    public function test_captcha_generation_stores_code_in_session(): void
    {
        $response = $this->actingAs($this->telecaller)->get(route('alerts.captcha-code'));
        $response->assertStatus(200);
        $response->assertJsonStructure(['code']);

        $this->assertNotNull(session('alert_captcha'));
        $this->assertEquals(2, strlen(session('alert_captcha')));
    }

    public function test_user_cannot_confirm_alert_with_invalid_captcha(): void
    {
        $alert = AppAlert::create([
            'heading' => 'URGENT ALERT',
            'title' => 'Read this.',
            'created_by' => $this->superAdmin->id
        ]);

        $alertUser = AppAlertUser::create([
            'app_alert_id' => $alert->id,
            'user_id' => $this->telecaller->id,
            'confirmed_at' => null
        ]);

        session(['alert_captcha' => '42']);

        $response = $this->actingAs($this->telecaller)->postJson(route('alerts.confirm'), [
            'alert_id' => $alert->id,
            'captcha' => '99' // wrong captcha
        ]);

        $response->assertStatus(422);
        $response->assertJsonFragment(['success' => false]);
        $this->assertNull($alertUser->fresh()->confirmed_at);
    }

    public function test_user_can_confirm_alert_with_valid_captcha(): void
    {
        $alert = AppAlert::create([
            'heading' => 'URGENT ALERT',
            'title' => 'Read this.',
            'created_by' => $this->superAdmin->id
        ]);

        $alertUser = AppAlertUser::create([
            'app_alert_id' => $alert->id,
            'user_id' => $this->telecaller->id,
            'confirmed_at' => null
        ]);

        session(['alert_captcha' => '42']);

        $response = $this->actingAs($this->telecaller)->postJson(route('alerts.confirm'), [
            'alert_id' => $alert->id,
            'captcha' => '42' // correct captcha
        ]);

        $response->assertStatus(200);
        $response->assertJsonFragment(['success' => true]);
        $this->assertNotNull($alertUser->fresh()->confirmed_at);
        $this->assertNull(session('alert_captcha')); // cleared from session
    }

    public function test_check_active_endpoint(): void
    {
        // 1. Without active alert
        $response = $this->actingAs($this->telecaller)->getJson(route('alerts.check-active'));
        $response->assertStatus(200);
        $response->assertJsonFragment(['has_alert' => false]);

        // 2. With active alert
        $alert = AppAlert::create([
            'heading' => 'POLL ALERT',
            'title' => 'Checking polling mechanism.',
            'created_by' => $this->superAdmin->id
        ]);
        AppAlertUser::create([
            'app_alert_id' => $alert->id,
            'user_id' => $this->telecaller->id,
            'confirmed_at' => null
        ]);

        $response = $this->actingAs($this->telecaller)->getJson(route('alerts.check-active'));
        $response->assertStatus(200);
        $response->assertJsonFragment([
            'has_alert' => true,
            'alert_id' => $alert->id,
            'heading' => 'POLL ALERT',
            'title' => 'Checking polling mechanism.'
        ]);
    }
}
