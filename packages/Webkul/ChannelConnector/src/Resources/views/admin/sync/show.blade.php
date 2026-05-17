<x-admin::layouts>
    <x-slot:title>
        @lang('channel_connector::app.sync.show.title') - {{ $job->job_id }}
    </x-slot>

    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            @lang('channel_connector::app.sync.show.title')
        </p>

        <div class="flex items-center gap-x-2.5">
            <a href="{{ route('admin.channel_connector.sync.index', $connector->code) }}" class="transparent-button">
                @lang('channel_connector::app.general.back')
            </a>
        </div>
    </div>

    <div class="mt-3.5 grid grid-cols-2 gap-4 lg:grid-cols-4">
        <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
            <p class="text-sm text-gray-500 dark:text-gray-400">@lang('channel_connector::app.sync.fields.status')</p>
            <p class="text-lg font-semibold text-gray-800 dark:text-white">{{ trans("channel_connector::app.sync.status.{$job->status}") }}</p>
        </div>
        <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
            <p class="text-sm text-gray-500 dark:text-gray-400">@lang('channel_connector::app.sync.fields.total-products')</p>
            <p class="text-lg font-semibold text-gray-800 dark:text-white">{{ $job->total_products }}</p>
        </div>
        <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
            <p class="text-sm text-gray-500 dark:text-gray-400">@lang('channel_connector::app.sync.fields.synced-products')</p>
            <p class="text-lg font-semibold text-green-600 dark:text-green-400">{{ $job->synced_products }}</p>
        </div>
        <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
            <p class="text-sm text-gray-500 dark:text-gray-400">@lang('channel_connector::app.sync.fields.failed-products')</p>
            <p class="text-lg font-semibold text-red-600 dark:text-red-400">{{ $job->failed_products }}</p>
        </div>
    </div>

    @if($job->total_products > 0)
        <div class="mt-4">
            <div class="h-4 w-full rounded-full bg-gray-200 dark:bg-gray-700">
                @php $pct = $job->total_products > 0 ? round(($job->synced_products + $job->failed_products) / $job->total_products * 100) : 0; @endphp
                <div class="h-4 rounded-full bg-blue-500" style="width: {{ $pct }}%"></div>
            </div>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $pct }}% @lang('channel_connector::app.sync.show.percent-complete')</p>
        </div>
    @endif

    @if(! empty($job->error_summary))
        <div class="mt-4">
            <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                <p class="mb-3 text-base font-semibold text-gray-800 dark:text-white">@lang('channel_connector::app.sync.errors.title')</p>
                <x-admin::table>
                    <x-admin::table.thead>
                        <x-admin::table.thead.tr>
                            <x-admin::table.th>@lang('channel_connector::app.sync.errors.product')</x-admin::table.th>
                            <x-admin::table.th>@lang('channel_connector::app.sync.errors.message')</x-admin::table.th>
                        </x-admin::table.thead.tr>
                    </x-admin::table.thead>
                    <x-admin::table.tbody>
                        @foreach($job->error_summary as $error)
                            <x-admin::table.tbody.tr>
                                <x-admin::table.td>{{ $error['product_sku'] ?? 'N/A' }}</x-admin::table.td>
                                <x-admin::table.td class="text-red-600 dark:text-red-400">{{ implode(', ', $error['errors'] ?? []) }}</x-admin::table.td>
                            </x-admin::table.tbody.tr>
                        @endforeach
                    </x-admin::table.tbody>
                </x-admin::table>
            </div>
        </div>
    @endif
</x-admin::layouts>
