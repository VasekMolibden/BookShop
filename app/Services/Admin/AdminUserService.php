<?php

namespace App\Services\Admin;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class AdminUserService
{
    /**
     * Paginated list of all users (newest first).
     */
    public function paginateAll(int $perPage = 15): LengthAwarePaginator
    {
        $perPage = min(max($perPage, 1), 100);

        return User::query()
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    /**
     * Update the administrator flag for a user.
     *
     * @throws ValidationException When this would leave the system without any admin.
     */
    public function setAdminFlag(User $target, bool $isAdmin): User
    {
        if (! $isAdmin && $target->is_admin) {
            $otherAdmins = User::query()
                ->where('is_admin', true)
                ->where('id', '!=', $target->id)
                ->exists();

            if (! $otherAdmins) {
                throw ValidationException::withMessages([
                    'is_admin' => ['Нельзя снять права администратора: в системе должен остаться хотя бы один администратор.'],
                ]);
            }
        }

        $target->is_admin = $isAdmin;
        $target->save();

        return $target->fresh();
    }
}
