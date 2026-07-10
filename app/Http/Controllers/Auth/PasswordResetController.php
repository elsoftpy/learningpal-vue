<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Models\User;
use App\Services\Utilities\ResponseService;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\App;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    public function sendResetLink(ForgotPasswordRequest $request): JsonResponse
    {
        $this->setRequestLocale($request->input('locale'));

        $status = Password::sendResetLink($request->only('email'));

        if ($status !== Password::ResetLinkSent) {
            return ResponseService::error(
                message: __($status),
                errors: ['email' => [__($status)]],
                statusCode: 422
            );
        }

        return ResponseService::success(
            message: __($status)
        );
    }

    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $this->setRequestLocale($request->input('locale'));

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password): void {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status !== Password::PasswordReset) {
            return ResponseService::error(
                message: __($status),
                errors: ['email' => [__($status)]],
                statusCode: 422
            );
        }

        return ResponseService::success(
            message: __($status)
        );
    }

    protected function setRequestLocale(?string $locale): void
    {
        $allowedLocales = config('app.available_locales', ['en', 'es', 'pt']);

        if (is_string($locale) && in_array($locale, $allowedLocales, true)) {
            App::setLocale($locale);
        }
    }
}
