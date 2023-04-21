<x-filament::widget>
    <x-filament::card>
        @isset ($title)
            <h2 class="text-lg sm:text-xl font-bold tracking-tight">{{ $title }}</h2>
        @endisset

        @isset ($subheading)
            <p class="text-xs">{{ $subheading }}</p>
        @endisset

        <table class="w-full text-start divide-y table-auto relative">
            <thead>
            <tr class="bg-gray-500/5">
                @foreach ($columns as $columnKey => $columnTitle)
                    <th class="py-2 font-medium text-sm text-gray-600 cursor-default text-center">
                        {{ $columnTitle }}
                    </th>
                @endforeach
            </tr>
            </thead>
            <tbody>
            @foreach ($rows as $row)
                @php
                    $name = $row['name'];
                    $status = $row['status'];
                    $checked = $status ? 'checked' : '';
                    $icon = $status ? 'heroicon-s-check-circle' : 'heroicon-s-x-circle';
                @endphp
                <tr>
                    <td class="text-xs py-2">{{ Arr::get($row, 'displayName') }}</td>
                    <td class="text-center">
                        <x-dynamic-component
                            :component="$icon"
                            @class([
                              "inline-block w-5 h-5",
                              "text-success-500" => $status,
                              "text-danger-500" => !$status
                            ])
                        />
                    </td>
                    <td class="flex items-center">
                        <x-filament::icon-button
                            wire:click="start('{{ $name }}')"
                            icon="heroicon-o-play"
                            :disabled="$status"
                        />

                        <x-filament::icon-button
                            wire:click="stop('{{ $name }}')"
                            icon="heroicon-o-pause"
                            :disabled="!$status"
                        />

                        <x-filament::icon-button
                            wire:click="restart('{{ $name }}')"
                            icon="heroicon-o-refresh"
                            :disabled="!$status"
                        />
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </x-filament::card>
</x-filament::widget>
