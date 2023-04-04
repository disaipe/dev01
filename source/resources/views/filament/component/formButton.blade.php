<x-dynamic-component
    :component="$getFieldWrapperView()"
    :id="$getId()"
    :label-sr-only="$isLabelHidden()"
    :helper-text="$getHelperText()"
    :hint="$getHint()"
    :hint-action="$getHintAction()"
    :hint-color="$getHintColor()"
    :hint-icon="$getHintIcon()"
    :required="$isRequired()"
    :state-path="$getStatePath()"
>
    <div
        x-data="{ state: $wire.entangle('{{ $getStatePath() }}') }"
    >
        <x-filament::button x-on:click="state = true">
            {{ $getLabel() }}
        </x-filament::button>
    </div>
</x-dynamic-component>
