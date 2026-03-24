<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthService
{
    /**
     * Register a new user and issue an API token.
     *
     * @param  array{name: string, email: string, password: string}  $data
     * @return array{user: User, token: string}
     */
    public function register(array $data): array
    {
        $user = User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
        ]);

        $token = $user->createToken('api')->plainTextToken;

        return ['user' => $user, 'token' => $token];
    }

    /**
     * Authenticate credentials and return a new API token.
     *
     * @return array{user: User, token: string}
     *
     * @throws AuthenticationException
     */
    public function login(string $email, string $password): array
    {
        $user = User::query()->where('email', $email)->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            throw new AuthenticationException('Неверный email или пароль.');
        }

        $user->tokens()->delete();
        $token = $user->createToken('api')->plainTextToken;

        return ['user' => $user, 'token' => $token];
    }

    /**
     * Revoke the current access token.
     */
    public function logout(User $user): void
    {
        $user->currentAccessToken()?->delete();
    }

    /**
     * Queue a password reset link email for the given address (broker status string).
     */
    public function sendPasswordResetLink(string $email): string
    {
        return Password::broker()->sendResetLink(['email' => $email]);
    }

    /**
     * Reset password using a valid token; revokes all API tokens for the user.
     *
     * @param  array{email: string, token: string, password: string, password_confirmation: string}  $credentials
     */
    public function resetPasswordWithToken(array $credentials): string
    {
        return Password::broker()->reset(
            $credentials,
            function (User $user, string $password): void {
                $user->forceFill([
                    'password' => $password,
                    'remember_token' => Str::random(60),
                ])->save();

                $user->tokens()->delete();
            }
        );
    }

    /**
     * Update profile fields (name and/or email).
     *
     * @param  array{name?: string, email?: string}  $data
     */
    public function updateProfile(User $user, array $data): User
    {
        if (isset($data['email']) && $data['email'] !== $user->email) {
            throw ValidationException::withMessages([
                'email' => ['Изменение email запрещено.'],
            ]);
        }

        // Only name can be changed here (email is immutable for now)
        $user->fill(array_filter($data, fn ($value, $key) => $key === 'name', ARRAY_FILTER_USE_BOTH));
        $user->save();

        return $user->fresh();
    }

    /**
     * Change password and revoke all API tokens (user must log in again).
     */
    public function updatePassword(User $user, string $newPassword): void
    {
        $user->forceFill([
            'password' => $newPassword,
            'remember_token' => Str::random(60),
        ])->save();

        $user->tokens()->delete();
    }
}
