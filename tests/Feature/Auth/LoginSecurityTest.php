<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Models\Role;
use App\Models\Department;
use App\Mail\WelcomeEmployeeMail;
use App\Mail\OtpMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class LoginSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_registration_sends_welcome_email(): void
    {
        Mail::fake();

        // Create standard roles & department needed for employee creation
        $role = Role::create(['name' => 'Employee', 'slug' => 'employee']);
        $dept = Department::create(['name' => 'Management', 'status' => 'active']);
        
        $admin = User::factory()->create([
            'role_id' => Role::create(['name' => 'Super Admin', 'slug' => 'super-admin'])->id,
        ]);

        $response = $this->actingAs($admin)->post(route('employees.store'), [
            'name' => 'Test Employee',
            'email' => 'newemployee@example.com',
            'role_id' => $role->id,
            'department_id' => $dept->id,
            'joining_date' => now()->toDateString(),
            'password' => 'CustomPassword123',
        ]);

        $response->assertRedirect(route('employees.index'));
        $this->assertDatabaseHas('users', ['email' => 'newemployee@example.com']);

        Mail::assertSent(WelcomeEmployeeMail::class, function ($mail) {
            return $mail->hasTo('newemployee@example.com') &&
                   $mail->password === 'CustomPassword123';
        });
    }

    public function test_multi_stage_login_protection_captcha_and_otp(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'email' => 'securitytest@example.com',
            'password' => bcrypt('CorrectPassword123'),
        ]);

        $email = $user->email;
        $cacheKey = 'login_attempts_' . $email;

        // Verify initial failure counts are zero
        $this->assertEquals(0, Cache::get($cacheKey, 0));

        // 1st failed attempt
        $response = $this->post('/login', [
            'email' => $email,
            'password' => 'wrong-password-1',
        ]);
        $response->assertSessionHasErrors('email');
        $this->assertEquals(1, Cache::get($cacheKey, 0));

        // 2nd failed attempt
        $response = $this->post('/login', [
            'email' => $email,
            'password' => 'wrong-password-2',
        ]);
        $response->assertSessionHasErrors('email');
        $this->assertEquals(2, Cache::get($cacheKey, 0));

        // 3rd failed attempt
        $response = $this->post('/login', [
            'email' => $email,
            'password' => 'wrong-password-3',
        ]);
        $response->assertSessionHasErrors('email');
        $this->assertEquals(3, Cache::get($cacheKey, 0));

        // Display login view - should now require captcha
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertViewHas('captcha_question');
        $this->assertTrue(session()->has('captcha_answer'));

        // 4th attempt with WRONG captcha should lock account and require OTP
        $response = $this->post('/login', [
            'email' => $email,
            'password' => 'CorrectPassword123',
            'captcha' => 9999, // Wrong captcha answer
        ]);
        
        $response->assertRedirect();
        $this->assertEquals(4, Cache::get($cacheKey, 0));

        // Verify OTP email sent
        Mail::assertSent(OtpMail::class, function ($mail) use ($email) {
            return $mail->hasTo($email);
        });

        $this->assertTrue(Cache::has('login_otp_' . $email));
        $otp = Cache::get('login_otp_' . $email);

        // Accessing login view now should force OTP view
        $response = $this->get('/login');
        $response->assertViewHas('otp_required', true);
        $response->assertViewHas('otp_email', $email);

        // Attempt OTP login with incorrect OTP code
        $response = $this->post('/login', [
            'email' => $email,
            'otp' => '000000',
        ]);
        $response->assertSessionHas('otp_required', true);
        $response->assertSessionHasErrors('otp');
        $this->assertGuest();

        // Attempt OTP login with correct OTP code
        $response = $this->post('/login', [
            'email' => $email,
            'otp' => $otp,
        ]);
        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);

        // Assert caches are cleared
        $this->assertFalse(Cache::has('login_attempts_' . $email));
        $this->assertFalse(Cache::has('login_otp_' . $email));
    }
}
