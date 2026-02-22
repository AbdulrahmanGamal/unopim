<?php

namespace Webkul\Tenant\Contracts;

interface TenantPermissionGuard
{
    /**
     * Check if a permission is platform-reserved.
     */
    public function isPlatformReserved(string $permission): bool;

    /**
     * Check if a user is a tenant-scoped user.
     */
    public function isTenantUser($user): bool;

    /**
     * Check if a user is a platform user.
     */
    public function isPlatformUser($user): bool;

    /**
     * Check if a user is allowed to have a specific permission.
     */
    public function isAllowed($user, string $permission): bool;

    /**
     * Filter a list of permissions to only those allowed for the user.
     */
    public function filterPermissions($user, array $permissions): array;
}
