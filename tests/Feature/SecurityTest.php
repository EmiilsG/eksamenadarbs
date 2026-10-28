<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_is_rate_limited_after_repeated_failures(): void
    {
        $user = User::factory()->create();

        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', [
                'email' => $user->email,
                'password' => 'wrong-password',
            ])->assertStatus(302);
        }

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ])->assertStatus(429);
    }

    public function test_password_reset_link_can_be_requested(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email])
            ->assertSessionHas('status');

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_password_can_be_reset_with_a_valid_token(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('old-password'),
        ]);

        $token = Password::createToken($user);

        $this->post('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
    }

    public function test_password_reset_fails_with_an_invalid_token(): void
    {
        $user = User::factory()->create();

        $this->post('/reset-password', [
            'token' => 'invalid-token',
            'email' => $user->email,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertSessionHasErrors('email');
    }

    public function test_unverified_user_cannot_create_a_product(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)->post('/products', [
            'name' => 'Testa produkts',
            'description' => 'Apraksts',
            'price' => 10,
        ])->assertRedirect(route('verification.notice'));

        $this->assertDatabaseCount('products', 0);
    }

    public function test_unverified_user_sees_a_verification_banner(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)->get('/products')
            ->assertOk()
            ->assertSee('Jūsu e-pasts nav apstiprināts');
    }

    public function test_email_can_be_verified_with_a_signed_link(): void
    {
        $user = User::factory()->unverified()->create();

        $url = URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), [
            'id' => $user->id,
            'hash' => sha1($user->email),
        ]);

        $this->actingAs($user)->get($url)->assertRedirect(route('dashboard'));

        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }

    public function test_verification_notice_page_is_available(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)->get('/email/verify')->assertOk();
    }

    public function test_registration_sends_a_verification_email_and_redirects_to_notice(): void
    {
        Notification::fake();

        $this->post('/register', [
            'name' => 'Jauns lietotājs',
            'email' => 'new-user@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('verification.notice'));

        $user = User::firstWhere('email', 'new-user@example.com');

        $this->assertAuthenticatedAs($user);
        $this->assertNull($user->email_verified_at);
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_forgot_password_page_renders(): void
    {
        $this->get('/forgot-password')->assertOk();
    }

    public function test_reset_password_page_renders(): void
    {
        $this->get('/reset-password/test-token?email=user@example.com')
            ->assertOk()
            ->assertSee('Jauna parole');
    }

    public function test_missing_page_uses_the_custom_404_view(): void
    {
        $this->get('/this-page-does-not-exist')
            ->assertNotFound()
            ->assertSee('Lapa nav atrasta');
    }
}
