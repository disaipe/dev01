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
    <x-filament::button wire:click="dispatchFormEvent('run')">
        {{ $getLabel() }}
    </x-filament::button>
</x-dynamic-component>
