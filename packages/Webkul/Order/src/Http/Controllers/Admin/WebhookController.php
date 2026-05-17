<?php

namespace Webkul\Order\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Channel\Repositories\ChannelRepository;
use Webkul\Order\DataGrids\Admin\WebhookDataGrid;
use Webkul\Order\Http\Requests\WebhookStoreRequest;
use Webkul\Order\Http\Requests\WebhookUpdateRequest;
use Webkul\Order\Repositories\WebhookRepository;

/**
 * Webhook Controller
 *
 * Manages webhook configurations for receiving order events
 * from external channels.
 */
class WebhookController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected WebhookRepository $webhookRepository,
        protected ChannelRepository $channelRepository
    ) {}

    /**
     * Display a listing of webhooks.
     */
    public function index(): View|JsonResponse
    {
        if (! bouncer()->allows('orders.webhooks.view')) {
            abort(403, trans('admin::app.errors.403'));
        }

        if (request()->ajax()) {
            return datagrid(WebhookDataGrid::class)->process();
        }

        return view('order::admin.webhooks.index');
    }

    /**
     * Show the form for creating a new webhook.
     */
    public function create(): View
    {
        if (! bouncer()->allows('orders.webhooks.create')) {
            abort(403, trans('admin::app.errors.403'));
        }

        $channels = $this->channelRepository->all();

        $eventTypes = config('order.webhook_events', [
            'order.created',
            'order.updated',
            'order.cancelled',
            'order.refunded',
            'order.fulfilled',
        ]);

        return view('order::admin.webhooks.create', [
            'channels'   => $channels,
            'eventTypes' => $eventTypes,
        ]);
    }

    /**
     * Store a newly created webhook in storage.
     */
    public function store(WebhookStoreRequest $request): RedirectResponse
    {
        if (! bouncer()->allows('orders.webhooks.create')) {
            abort(403, trans('admin::app.errors.403'));
        }

        $data = $request->validated();
        $data['is_active'] = $request->has('is_active');
        $data['secret_key'] = bin2hex(random_bytes(32));

        $this->webhookRepository->create($data);

        return redirect()->route('admin.orders.webhooks.index')
            ->with('success', trans('order::app.admin.webhooks.create-success'));
    }

    /**
     * Show the form for editing the specified webhook.
     */
    public function edit(int $id): View
    {
        if (! bouncer()->allows('orders.webhooks.edit')) {
            abort(403, trans('admin::app.errors.403'));
        }

        $webhook = $this->webhookRepository->findOrFail($id);
        $channels = $this->channelRepository->all();

        $eventTypes = config('order.webhook_events', [
            'order.created',
            'order.updated',
            'order.cancelled',
            'order.refunded',
            'order.fulfilled',
        ]);

        return view('order::admin.webhooks.edit', [
            'webhook'    => $webhook,
            'channels'   => $channels,
            'eventTypes' => $eventTypes,
        ]);
    }

    /**
     * Update the specified webhook in storage.
     */
    public function update(WebhookUpdateRequest $request, int $id): RedirectResponse
    {
        if (! bouncer()->allows('orders.webhooks.edit')) {
            abort(403, trans('admin::app.errors.403'));
        }

        $data = $request->validated();
        $data['is_active'] = $request->has('is_active');

        $this->webhookRepository->update($data, $id);

        return redirect()->route('admin.orders.webhooks.index')
            ->with('success', trans('order::app.admin.webhooks.update-success'));
    }

    /**
     * Remove the specified webhook from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        if (! bouncer()->allows('orders.webhooks.delete')) {
            return response()->json([
                'message' => trans('admin::app.errors.403'),
            ], 403);
        }

        try {
            $this->webhookRepository->delete($id);

            return response()->json([
                'message' => trans('order::app.admin.webhooks.delete-success'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => trans('order::app.admin.webhooks.delete-failed'),
            ], 500);
        }
    }

    /**
     * Toggle webhook active status.
     */
    public function toggleStatus(int $id): JsonResponse
    {
        if (! bouncer()->allows('orders.webhooks.edit')) {
            return response()->json([
                'message' => trans('admin::app.errors.403'),
            ], 403);
        }

        try {
            $webhook = $this->webhookRepository->findOrFail($id);

            $this->webhookRepository->update([
                'is_active' => ! $webhook->is_active,
            ], $id);

            return response()->json([
                'message'   => trans('order::app.admin.webhooks.status-updated'),
                'is_active' => ! $webhook->is_active,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => trans('order::app.admin.webhooks.status-update-failed'),
            ], 500);
        }
    }
}
