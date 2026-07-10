<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\App;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ForgotPasswordFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_sends_a_password_reset_link_to_a_known_user(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $originalLocale = App::currentLocale();

        try {
            $response = $this->postJson(route('api.v1.auth.password.email'), [
                'email' => $user->email,
                'locale' => 'es',
            ]);

            $response->assertOk()
                ->assertJson([
                    'success' => true,
                    'message' => 'Hemos enviado por correo electrónico el enlace para restablecer su contraseña.',
                    'data' => [],
                    'errors' => [],
                ]);

            Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification, array $channels) use ($user): bool {
                $mailMessage = $notification->toMail($user);

                return in_array('mail', $channels, true)
                    && $mailMessage->subject === 'Notificación de restablecimiento de contraseña'
                    && str_contains($mailMessage->actionUrl, '/reset-password')
                    && str_contains($mailMessage->actionUrl, 'email='.rawurlencode($user->email))
                    && $mailMessage->actionText === 'Restablecer contraseña';
            });
        } finally {
            App::setLocale($originalLocale);
        }
    }

    public function test_it_returns_a_validation_error_for_an_unknown_email(): void
    {
        $response = $this->postJson(route('api.v1.auth.password.email'), [
            'email' => 'missing@example.com',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => __('passwords.user'),
                'data' => null,
                'errors' => [
                    'email' => [__('passwords.user')],
                ],
            ]);
    }

    public function test_it_resets_the_password_with_a_valid_token(): void
    {
        $user = User::factory()->create();

        $token = app('auth.password.broker')->createToken($user);

        $response = $this->postJson(route('api.v1.auth.password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => __('passwords.reset'),
                'data' => [],
                'errors' => [],
            ]);

        $this->assertTrue(Hash::check('new-password', $user->refresh()->password));
    }

    public function test_it_rejects_an_invalid_reset_token(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson(route('api.v1.auth.password.update'), [
            'token' => 'invalid-token',
            'email' => $user->email,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => __('passwords.token'),
                'data' => null,
                'errors' => [
                    'email' => [__('passwords.token')],
                ],
            ]);
    }
}
