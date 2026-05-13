<?php

namespace Webkul\User;

use Webkul\Tenant\Contracts\TenantPermissionGuard;

class Bouncer
{
    /**
     * Checks if user allowed or not for certain action
     *
     * @param  string  $permission
     * @return void
     */
    public function hasPermission($permission)
    {
        if (! auth()->guard('admin')->check()) {
            return false;
        }

        $user = auth()->guard('admin')->user();

        if ($user->role->permission_type == 'all') {
            // Tenant users with "all" still cannot access platform-reserved permissions (Story 5.5)
            $guard = app(TenantPermissionGuard::class);

            return $guard->isAllowed($user, $permission);
        }

        return $user->hasPermission($permission);
    }

    /**
     * Checks if user allowed or not for certain action
     *
     * @param  string  $permission
     * @return void
     */
    public static function allow($permission)
    {
        if (! auth()->guard('admin')->check()) {
            abort(401, 'This action is unauthorized');
        }

        $user = auth()->guard('admin')->user();

        if ($user->role->permission_type == 'all') {
            $guard = app(TenantPermissionGuard::class);

            if (! $guard->isAllowed($user, $permission)) {
                abort(403, 'This action is unauthorized');
            }

            return;
        }

        if (! $user->hasPermission($permission)) {
            abort(403, 'This action is unauthorized');
        }
    }
}
