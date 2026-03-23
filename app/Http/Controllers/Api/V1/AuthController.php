<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ForgotPasswordRequest;
use App\Http\Requests\Api\V1\LoginRequest;
use App\Http\Requests\Api\V1\RegisterRequest;
use App\Http\Requests\Api\V1\ResetPasswordRequest;
use App\Http\Requests\Api\V1\UpdatePasswordRequest;
use App\Http\Requests\Api\V1\UpdateProfileRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Services\AuthService;
use App\Support\ApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService
    ) {}

    /**
     * Register a new user account.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $result = $this->authService->register($request->safe()->only(['name', 'email', 'password']));

        return ApiResponse::success([
            'user' => new UserResource($result['user']),
            'token' => $result['token'],
            'token_type' => 'Bearer',
        ], 'Регистрация прошла успешно', 201);
    }

    /**
     * Exchange credentials for an API token.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->login(
                $request->validated('email'),
                $request->validated('password')
            );
        } catch (AuthenticationException $e) {
            return ApiResponse::error(401, $e->getMessage(), null, 401);
        }

        return ApiResponse::success([
            'user' => new UserResource($result['user']),
            'token' => $result['token'],
            'token_type' => 'Bearer',
        ], 'Вход выполнен успешно');
    }

    /**
     * Invalidate the current access token.
     */
    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return ApiResponse::success(null, 'Выход выполнен успешно');
    }

    /**
     * Return the authenticated user profile.
     */
    public function me(Request $request): JsonResponse
    {
        return ApiResponse::success([
            'user' => new UserResource($request->user()),
        ], 'Профиль пользователя');
    }

    /**
     * Send a password reset link to the email address.
     */
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $status = $this->authService->sendPasswordResetLink($request->validated('email'));

        if ($status === Password::RESET_THROTTLED) {
            return ApiResponse::error(
                429,
                'Слишком частые запросы. Подождите минуту и попробуйте снова.',
                null,
                429
            );
        }

        return ApiResponse::success(
            null,
            'Если указанный email зарегистрирован, на него отправлена ссылка для сброса пароля.'
        );
    }

    /**
     * Reset password using token from the email link.
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $status = $this->authService->resetPasswordWithToken($request->validated());

        if ($status === Password::PASSWORD_RESET) {
            return ApiResponse::success(null, 'Пароль успешно изменён. Теперь можно войти.');
        }

        $message = match ($status) {
            Password::INVALID_TOKEN => 'Ссылка недействительна или срок её действия истёк. Запросите новую.',
            Password::INVALID_USER => 'Не удалось сбросить пароль для этого адреса.',
            Password::RESET_THROTTLED => 'Слишком много попыток. Подождите и повторите позже.',
            default => 'Не удалось сбросить пароль.',
        };

        return ApiResponse::error(422, $message, null, 422);
    }

    /**
     * Update name and/or email for the authenticated user.
     */
    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        $user = $this->authService->updateProfile(
            $request->user(),
            $request->safe()->only(['name', 'email'])
        );

        return ApiResponse::success([
            'user' => new UserResource($user),
        ], 'Профиль обновлён');
    }

    /**
     * Change password; all API tokens are revoked.
     */
    public function updatePassword(UpdatePasswordRequest $request): JsonResponse
    {
        $this->authService->updatePassword(
            $request->user(),
            $request->validated('password')
        );

        return ApiResponse::success(null, 'Пароль изменён. Войдите снова — прежние сессии отключены.');
    }
}
