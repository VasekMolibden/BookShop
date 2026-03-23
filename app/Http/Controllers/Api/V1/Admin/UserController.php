<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\AdminUserListRequest;
use App\Http\Requests\Api\V1\Admin\UpdateAdminUserRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\User;
use App\Services\Admin\AdminUserService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    public function __construct(
        private readonly AdminUserService $userService
    ) {}

    /**
     * Paginated list of registered users.
     */
    public function index(AdminUserListRequest $request): JsonResponse
    {
        $perPage = (int) ($request->validated('per_page') ?? 15);
        $paginator = $this->userService->paginateAll($perPage);

        return ApiResponse::success([
            'users' => UserResource::collection($paginator->items()),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ], 'Пользователи');
    }

    /**
     * Update admin flag for a user.
     */
    public function update(UpdateAdminUserRequest $request, User $user): JsonResponse
    {
        $user = $this->userService->setAdminFlag($user, (bool) $request->validated('is_admin'));

        return ApiResponse::success([
            'user' => new UserResource($user),
        ], 'Пользователь обновлён');
    }
}
