<?php

namespace Webkul\Tenant\Services;

use Webkul\Tenant\Models\Tenant;
use Webkul\User\Contracts\Admin as AdminContract;

/**
 * Encapsulates the rules for a platform operator switching the active tenant
 * context. Extracted from TenantController to keep the controller thin and to
 * make the rules unit-testable without the HTTP layer.
 */
class TenantContextSwitcher
{
    public const RESULT_OK = 'ok';

    public const RESULT_FORBIDDEN = 'forbidden';

    public const RESULT_NOT_FOUND = 'not_found';

    public const RESULT_CLEARED = 'cleared';

    /**
     * Switch the session's tenant context.
     *
     * @param  AdminContract|object  $admin  Currently authenticated admin (admin guard user).
     * @param  int|null  $tenantId  Tenant to switch to, or null to clear to the platform view.
     * @return array{status: string, tenant?: Tenant}
     */
    public function switchTo(object $admin, ?int $tenantId): array
    {
        // Only platform operators (admins without a tenant_id) may switch context.
        if (! empty($admin->tenant_id)) {
            return ['status' => self::RESULT_FORBIDDEN];
        }

        if ($tenantId === null) {
            session()->forget('tenant_context_id');
            core()->setCurrentTenantId(null);

            return ['status' => self::RESULT_CLEARED];
        }

        $tenant = Tenant::where('id', $tenantId)
            ->where('status', Tenant::STATUS_ACTIVE)
            ->first();

        if (! $tenant) {
            return ['status' => self::RESULT_NOT_FOUND];
        }

        session(['tenant_context_id' => (int) $tenant->id]);
        core()->setCurrentTenantId($tenant->id);

        return ['status' => self::RESULT_OK, 'tenant' => $tenant];
    }
}
