<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\WorkSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\RoleSeeder;

class DailyReportTodaySessionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_daily_reports_page_displays_today_work_sessions()
    {
        $this->seed(RoleSeeder::class);
        $employeeRole = Role::where('slug', 'employee')->first();
        $adminRole = Role::where('slug', 'admin')->first();

        $employee = User::factory()->create(['role_id' => $employeeRole->id, 'name' => 'Alice Employee']);
        $admin = User::factory()->create(['role_id' => $adminRole->id, 'name' => 'Bob Admin']);

        // Create today's work session for Alice Employee
        WorkSession::create([
            'user_id' => $employee->id,
            'date' => today(),
            'started_at' => now()->subHours(2),
            'ended_at' => now(),
            'total_minutes' => 120,
            'status' => 'completed',
            'work_done' => 'Designed the database schema and created views',
        ]);

        // Load daily-reports index as Alice (Employee)
        $response = $this->actingAs($employee)
            ->get(route('daily-reports.index'));

        $response->assertStatus(200);
        $response->assertSee("Today's Done Works & Sessions", false);
        $response->assertSee('Designed the database schema and created views');

        // Load daily-reports index as Bob (Admin)
        $response2 = $this->actingAs($admin)
            ->get(route('daily-reports.index'));

        $response2->assertStatus(200);
        $response2->assertSee("Today's Done Works & Sessions", false);
        $response2->assertSee('Alice Employee');
        $response2->assertSee('Designed the database schema and created views');

        // Load daily-reports index as Charlie (Team Leader)
        $teamLeaderRole = Role::where('slug', 'team-leader')->first();
        $charlieTl = User::factory()->create(['role_id' => $teamLeaderRole->id, 'name' => 'Charlie TL']);

        $response3 = $this->actingAs($charlieTl)
            ->get(route('daily-reports.index'));

        $response3->assertStatus(200);
        // Charlie should not see Alice Employee's work session because Charlie is a Team Leader and not an Admin
        $response3->assertDontSee('Alice Employee');
        $response3->assertDontSee('Designed the database schema and created views');
    }
}
