<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use App\Models\Holiday;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HolidayMarkingTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

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
    }

    public function test_admin_can_view_holiday_marking_page(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('settings.holidays.index'));

        $response->assertStatus(200);
        $response->assertViewIs('settings.holidays.index');
    }

    public function test_admin_can_mark_holiday(): void
    {
        $data = [
            'name' => 'New Year Day',
            'date' => '2026-01-01',
            'type' => 'national',
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('settings.holidays.store'), $data);

        $response->assertRedirect(route('settings.holidays.index'));
        $this->assertDatabaseHas('holidays', [
            'name' => 'New Year Day',
            'date' => '2026-01-01 00:00:00',
            'type' => 'national',
        ]);
     }

     public function test_admin_can_mark_recurring_holiday(): void
     {
         // Click 2nd Saturday of July 2026: July 11th, 2026
         $data = [
             'name' => 'Second Saturday Office Off',
             'date' => '2026-07-11',
             'type' => 'company',
             'repeat_yearly_nth_day' => '1',
         ];

         $response = $this->actingAs($this->admin)
             ->post(route('settings.holidays.store'), $data);

         $response->assertRedirect(route('settings.holidays.index'));
         
         // Verify it created holidays for all months in 2026.
         // Let's assert database has the second Saturday of August 2026 (Aug 8th, 2026)
         $this->assertDatabaseHas('holidays', [
             'name' => 'Second Saturday Office Off',
             'date' => '2026-08-08 00:00:00',
             'type' => 'company',
         ]);

         // Let's assert database has the second Saturday of December 2026 (Dec 12th, 2026)
         $this->assertDatabaseHas('holidays', [
             'name' => 'Second Saturday Office Off',
             'date' => '2026-12-12 00:00:00',
             'type' => 'company',
         ]);
     }

    public function test_admin_can_delete_holiday(): void
    {
        $holiday = Holiday::create([
            'name' => 'Labor Day',
            'date' => '2026-05-01',
            'type' => 'company',
        ]);

        $response = $this->actingAs($this->admin)
            ->delete(route('settings.holidays.destroy', $holiday));

        $response->assertRedirect(route('settings.holidays.index'));
        $this->assertDatabaseMissing('holidays', [
            'id' => $holiday->id,
        ]);
    }
}
