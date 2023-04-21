@php
    $event = $getEventName();
    $name = $getName();
@endphp


@if ($isPageEvent())
    <x-filament::button
        wire:click="{{ $event }}()"
    >
        {{ $getLabel() }}
    </x-filament::button>
@else
    <x-filament::button
        wire:click="dispatchFormEvent('{{ $event }}', '{{ $name }}')"
    >
        {{ $getLabel() }}
    </x-filament::button>
@endif
