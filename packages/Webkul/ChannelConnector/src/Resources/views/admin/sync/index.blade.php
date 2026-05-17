<x-admin::layouts>
    <x-slot:title>
        @lang('channel_connector::app.sync.index.title') - {{ $connector->name }}
    </x-slot>

    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            @lang('channel_connector::app.sync.index.title') - {{ $connector->name }}
        </p>

        <div class="flex items-center gap-x-2.5">
            <x-admin::form
                as="form"
                :action="route('admin.channel_connector.sync.trigger', $connector->code)"
                method="POST"
                class="flex gap-2"
            >
                @csrf
                <select name="sync_type" class="rounded border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                    <option value="incremental">@lang('channel_connector::app.sync.types.incremental')</option>
                    <option value="full">@lang('channel_connector::app.sync.types.full')</option>
                </select>
                <button type="submit" class="primary-button">@lang('channel_connector::app.sync.actions.trigger-sync')</button>
            </x-admin::form>
        </div>
    </div>

    <div class="mt-3.5">
        <div class="box-shadow rounded bg-white dark:bg-gray-900">
            <x-admin::table>
                <x-admin::table.thead>
                    <x-admin::table.thead.tr>
                        <x-admin::table.th>@lang('channel_connector::app.sync.fields.sync-type')</x-admin::table.th>
                        <x-admin::table.th>@lang('channel_connector::app.sync.fields.status')</x-admin::table.th>
                        <x-admin::table.th>@lang('channel_connector::app.sync.fields.progress')</x-admin::table.th>
                        <x-admin::table.th>@lang('channel_connector::app.sync.fields.started-at')</x-admin::table.th>
                        <x-admin::table.th>@lang('channel_connector::app.general.actions')</x-admin::table.th>
                    </x-admin::table.thead.tr>
                </x-admin::table.thead>
                <x-admin::table.tbody>
                    @forelse($jobs as $job)
                        <x-admin::table.tbody.tr>
                            <x-admin::table.td>{{ trans("channel_connector::app.sync.types.{$job->sync_type}") }}</x-admin::table.td>
                            <x-admin::table.td>
                                <span class="rounded px-2 py-0.5 text-xs font-medium
                                    {{ $job->status === 'completed' ? 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300' : '' }}
                                    {{ $job->status === 'failed' ? 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300' : '' }}
                                    {{ $job->status === 'running' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300' : '' }}
                                    {{ $job->status === 'pending' ? 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300' : '' }}
                                    {{ $job->status === 'retrying' ? 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-300' : '' }}
                                ">{{ trans("channel_connector::app.sync.status.{$job->status}") }}</span>
                            </x-admin::table.td>
                            <x-admin::table.td>{{ $job->synced_products }}/{{ $job->total_products }} ({{ $job->failed_products }} @lang('channel_connector::app.sync.fields.failed-products'))</x-admin::table.td>
                            <x-admin::table.td>{{ $job->started_at?->diffForHumans() ?? '-' }}</x-admin::table.td>
                            <x-admin::table.td>
                                <a href="{{ route('admin.channel_connector.sync.show', [$connector->code, $job->job_id]) }}" class="text-blue-600 hover:underline dark:text-blue-400">@lang('channel_connector::app.general.view')</a>
                            </x-admin::table.td>
                        </x-admin::table.tbody.tr>
                    @empty
                        <x-admin::table.tbody.tr>
                            <x-admin::table.td colspan="5" class="text-center text-gray-500">@lang('channel_connector::app.sync.index.empty')</x-admin::table.td>
                        </x-admin::table.tbody.tr>
                    @endforelse
                </x-admin::table.tbody>
            </x-admin::table>
        </div>

        @if($jobs->hasPages())
            <div class="mt-4">{{ $jobs->links() }}</div>
        @endif
    </div>
</x-admin::layouts>
