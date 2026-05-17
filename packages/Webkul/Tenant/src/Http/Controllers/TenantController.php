<?php

namespace Webkul\Tenant\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Webkul\Tenant\DataGrids\TenantDataGrid;
use Webkul\Tenant\Models\Tenant;
use Webkul\Tenant\Repositories\TenantRepository;
use Webkul\Tenant\Services\TenantContextSwitcher;
use Webkul\Tenant\Services\TenantPurger;
use Webkul\Tenant\Services\TenantSeeder;

class TenantController extends Controller
{
    public function __construct(
        protected TenantRepository $tenantRepository,
        protected TenantSeeder $tenantSeeder,
        protected TenantPurger $tenantPurger,
        protected TenantContextSwitcher $contextSwitcher,
    ) {}

    /**
     * List all tenants (DataGrid).
     */
    public function index()
    {
        if (request()->ajax()) {
            return app(TenantDataGrid::class)->toJson();
        }

        return view('tenant::settings.tenants.index');
    }

    /**
     * Show create tenant form.
     */
    public function create()
    {
        return view('tenant::settings.tenants.create');
    }

    /**
     * Store a new tenant.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'domain'      => 'required|string|max:255|unique:tenants,domain',
            'admin_email' => 'required|email|max:255',
        ]);

        Event::dispatch('tenant.create.before');

        try {
            $tenant = $this->tenantRepository->create([
                'uuid'          => (string) Str::uuid(),
                'name'          => $request->input('name'),
                'domain'        => $request->input('domain'),
                'status'        => Tenant::STATUS_PROVISIONING,
                'es_index_uuid' => (string) Str::uuid(),
            ]);

            $this->tenantSeeder->seed($tenant, [
                'email' => $request->input('admin_email'),
            ]);

            $tenant->transitionTo(Tenant::STATUS_ACTIVE);

            Event::dispatch('tenant.create.after', $tenant);

            session()->flash('success', trans('tenant::app.tenants.create-success'));

            return redirect()->route('admin.settings.tenants.index');
        } catch (\Throwable $e) {
            session()->flash('error', trans('tenant::app.tenants.create-failed', ['error' => $e->getMessage()]));

            return redirect()->back()->withInput();
        }
    }

    /**
     * Show tenant details.
     */
    public function show(int $id)
    {
        $tenant = $this->tenantRepository->findOrFail($id);

        return view('tenant::settings.tenants.show', compact('tenant'));
    }

    /**
     * Show edit form.
     */
    public function edit(int $id)
    {
        $tenant = $this->tenantRepository->findOrFail($id);

        return view('tenant::settings.tenants.edit', compact('tenant'));
    }

    /**
     * Update tenant.
     */
    public function update(Request $request, int $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        Event::dispatch('tenant.update.before', $id);

        $tenant = $this->tenantRepository->update($request->only('name', 'settings'), $id);

        Event::dispatch('tenant.update.after', $tenant);

        session()->flash('success', trans('tenant::app.tenants.update-success'));

        return redirect()->route('admin.settings.tenants.edit', $id);
    }

    /**
     * Delete tenant (purge + delete).
     */
    public function destroy(int $id): JsonResponse
    {
        $tenant = $this->tenantRepository->findOrFail($id);

        if ($tenant->status === Tenant::STATUS_PROVISIONING) {
            return new JsonResponse([
                'message' => trans('tenant::app.tenants.cannot-delete-provisioning'),
            ], 400);
        }

        try {
            Event::dispatch('tenant.delete.before', $id);

            $tenant->transitionTo(Tenant::STATUS_DELETING);
            $this->tenantPurger->purge($tenant);
            $tenant->transitionTo(Tenant::STATUS_DELETED);

            Event::dispatch('tenant.delete.after', $id);

            return new JsonResponse([
                'message' => trans('tenant::app.tenants.delete-success'),
            ]);
        } catch (\Throwable $e) {
            return new JsonResponse([
                'message' => trans('tenant::app.tenants.delete-failed'),
            ], 500);
        }
    }

    /**
     * Suspend a tenant.
     */
    public function suspend(int $id): JsonResponse
    {
        $tenant = $this->tenantRepository->findOrFail($id);

        try {
            $tenant->transitionTo(Tenant::STATUS_SUSPENDED);

            return new JsonResponse([
                'message' => trans('tenant::app.tenants.suspend-success'),
            ]);
        } catch (\Throwable $e) {
            return new JsonResponse([
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Activate a tenant.
     */
    public function activate(int $id): JsonResponse
    {
        $tenant = $this->tenantRepository->findOrFail($id);

        try {
            $tenant->transitionTo(Tenant::STATUS_ACTIVE);

            return new JsonResponse([
                'message' => trans('tenant::app.tenants.activate-success'),
            ]);
        } catch (\Throwable $e) {
            return new JsonResponse([
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Switch tenant context for platform operators.
     *
     * Stores the selected tenant ID in the session so that
     * TenantMiddleware picks it up on subsequent requests.
     */
    public function switchContext(Request $request): JsonResponse
    {
        $admin = auth()->guard('admin')->user();
        $tenantId = $request->input('tenant_id');
        $tenantId = $tenantId !== null && $tenantId !== '' ? (int) $tenantId : null;

        $result = $this->contextSwitcher->switchTo($admin, $tenantId);

        return match ($result['status']) {
            TenantContextSwitcher::RESULT_FORBIDDEN => new JsonResponse([
                'message' => trans('tenant::app.tenants.switch-forbidden'),
            ], 403),
            TenantContextSwitcher::RESULT_NOT_FOUND => new JsonResponse([
                'message' => trans('tenant::app.tenants.switch-not-found'),
            ], 404),
            TenantContextSwitcher::RESULT_CLEARED => new JsonResponse([
                'message' => trans('tenant::app.tenants.switch-cleared'),
                'tenant'  => null,
            ]),
            TenantContextSwitcher::RESULT_OK => new JsonResponse([
                'message' => trans('tenant::app.tenants.switch-success', ['name' => $result['tenant']->name]),
                'tenant'  => [
                    'id'   => $result['tenant']->id,
                    'name' => $result['tenant']->name,
                ],
            ]),
        };
    }
}
