<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Mail\PasswordResetOtpMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_forgot_password_screen_can_be_rendered(): void
    {
        $response = $this->get('/forgot-password');

        $response->assertStatus(200);
    }

    public function test_forgot_password_otp_can_be_requested(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'email' => 'ramiz@teamtechsoul.com',
        ]);

        $response = $this->post('/forgot-password', [
            'email' => $user->email,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('reset_email', $user->email);
        $response->assertSessionHas('reset_otp_sent', true);

        Mail::assertSent(PasswordResetOtpMail::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });

        $this->assertNotNull(Cache::get('password_reset_otp_' . $user->email));
    }

    public function test_forgot_password_fails_for_non_existent_email(): void
    {
        Mail::fake();

        $response = $this->post('/forgot-password', [
            'email' => 'nonexistent@example.com',
        ]);

        $response->assertSessionHasErrors(['email']);
        Mail::assertNothingSent();
    }

    public function test_password_can_be_reset_with_valid_otp(): void
    {
        $user = User::factory()->create([
            'email' => 'ramiz@teamtechsoul.com',
            'password' => Hash::make('OldPassword@123'),
        ]);

        $otp = '123456';
        Cache::put('password_reset_otp_' . $user->email, $otp, now()->addMinutes(10));

        $response = $this->withSession([
            'reset_email' => $user->email,
            'reset_otp_sent' => true,
        ])->post('/forgot-password', [
            'email' => $user->email,
            'otp' => $otp,
            'password' => 'NewPassword@123',
            'password_confirmation' => 'NewPassword@123',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('status');

        $this->assertTrue(Hash::check('NewPassword@123', $user->fresh()->password));
        $this->assertNull(Cache::get('password_reset_otp_' . $user->email));
    }

    public function test_password_cannot_be_reset_with_invalid_otp(): void
    {
        $user = User::factory()->create([
            'email' => 'ramiz@teamtechsoul.com',
            'password' => Hash::make('OldPassword@123'),
        ]);

        $otp = '123456';
        Cache::put('password_reset_otp_' . $user->email, $otp, now()->addMinutes(10));

        $response = $this->withSession([
            'reset_email' => $user->email,
            'reset_otp_sent' => true,
        ])->post('/forgot-password', [
            'email' => $user->email,
            'otp' => '654321', // wrong OTP
            'password' => 'NewPassword@123',
            'password_confirmation' => 'NewPassword@123',
        ]);

        $response->assertSessionHasErrors(['otp']);
        $this->assertTrue(Hash::check('OldPassword@123', $user->fresh()->password));
    }
}
