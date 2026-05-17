<x-admin::layouts>
    <x-slot:title>
        @lang('channel_connector::app.mappings.preview') - {{ $connector->name }}
    </x-slot>

    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            @lang('channel_connector::app.mappings.preview') - {{ $connector->name }}
        </p>

        <a href="{{ route('admin.channel_connector.mappings.index', $connector->code) }}" class="transparent-button">
            @lang('channel_connector::app.general.back')
        </a>
    </div>

    <div class="mt-3.5">
        <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
            <x-admin::table>
                <x-admin::table.thead>
                    <x-admin::table.thead.tr>
                        <x-admin::table.th>@lang('channel_connector::app.mappings.fields.unopim-attribute')</x-admin::table.th>
                        <x-admin::table.th>@lang('channel_connector::app.mappings.fields.channel-field')</x-admin::table.th>
                        <x-admin::table.th>@lang('channel_connector::app.mappings.fields.direction')</x-admin::table.th>
                        <x-admin::table.th>@lang('channel_connector::app.mappings.fields.locale-mapping')</x-admin::table.th>
                    </x-admin::table.thead.tr>
                </x-admin::table.thead>
                <x-admin::table.tbody>
                    @foreach($mappings as $mapping)
                        <x-admin::table.tbody.tr>
                            <x-admin::table.td>{{ $mapping->unopim_attribute_code }}</x-admin::table.td>
                            <x-admin::table.td>{{ $mapping->channel_field }}</x-admin::table.td>
                            <x-admin::table.td>
                                <span class="rounded bg-gray-100 px-2 py-0.5 text-xs dark:bg-gray-700">
                                    {{ trans("channel_connector::app.mappings.direction.{$mapping->direction}") }}
                                </span>
                            </x-admin::table.td>
                            <x-admin::table.td>
                                @if($mapping->locale_mapping)
                                    @foreach($mapping->locale_mapping as $from => $to)
                                        <span class="text-xs">{{ $from }} &rarr; {{ $to }}</span><br>
                                    @endforeach
                                @else
                                    -
                                @endif
                            </x-admin::table.td>
                        </x-admin::table.tbody.tr>
                    @endforeach
                </x-admin::table.tbody>
            </x-admin::table>
        </div>
    </div>
</x-admin::layouts>
