<x-dynamic-component
    :component="$getFieldWrapperView()"
    :id="$getId()"
    :label="$getLabel()"
    :label-sr-only="$isLabelHidden()"
    :helper-text="$getHelperText()"
    :hint="$getHint()"
    :hint-action="$getHintAction()"
    :hint-color="$getHintColor()"
    :hint-icon="$getHintIcon()"
    :required="$isRequired()"
    :state-path="$getStatePath()"
>
    <script type="application/javascript">
        function parseCron() {
            if (this.state) {
                try {
                    this.description = cronstrue.toString(this.state, {
                        use24HourTimeFormat: true,
                        locale: '{{ $locale }}'
                    });
                    this.valid = true;
                } catch (e) {
                    this.description = null;
                    this.valid = false;
                }
            } else {
                this.description = null;
                this.valid = true;
            }
        }

        function selectPart() {
            if (this.state) {
                this.selectedPart = this.state.substring(0, this.$refs.input.selectionStart).split(' ').length || 0;
            } else {
                this.selectedPart = 0;
            }
        }
    </script>

    <div x-data="{
            state: $wire.entangle('{{ $getStatePath() }}').defer,
            description: null,
            selectedPart: 0,
            valid: true,
            parseCron,
            selectPart
        }"

        x-init="$nextTick(() => parseCron())"
    >
        <script src="https://unpkg.com/cronstrue@latest/dist/cronstrue.min.js"></script>
        <script src="https://unpkg.com/cronstrue@latest/locales/{{ $locale  }}.js"></script>

        <div class="flex space-x-4">
            <div class="flex flex-col">
                <input
                    {!! $isDisabled() ? 'disabled' : null !!}
                    {!! ($placeholder = $getPlaceholder()) ? "placeholder=\"{$placeholder}\"" : null !!}
                    x-ref="input"
                    x-model="state"
                    x-on:input="parseCron"
                    x-on:keyup="selectPart"
                    x-on:mouseup="selectPart"
                    type="text"
                    style="word-spacing: 12px"
                    class="block transition duration-75 rounded-lg shadow-sm outline-none focus:border-primary-500 focus:ring-1 focus:ring-inset focus:ring-primary-500 disabled:opacity-70 text-center"
                    x-bind:class="{
                        'border-gray-300': valid,
                        'dark:border-gray-600': valid && @js(config('forms.dark_mode')),
                        'border-danger-600 ring-danger-600': !valid,
                        'dark:border-danger-400 dark:ring-danger-400': !valid && @js(config('forms.dark_mode')),
                    }"
                />
                <div class="grid grid-cols-5 text-xs text-center font-mono">
                    @foreach(['min', 'hour', 'day<br>(month)', 'month', 'day<br>(week)'] as $i => $part)
                        <div x-bind:style="selectedPart == {!! $i + 1 !!} && { color: '#ff6a00' }">{!! $part !!}</div>
                    @endforeach
                </div>
            </div>

            <div
                class="w-8 h-8 leading-8 mt-2 bg-danger-600 text-center rounded-full text-white text-lg font-bold"
                x-bind:class="{ hidden: valid }"
            >!</div>

            <div x-text="description" class="text-sm"></div>
        </div>
    </div>
</x-dynamic-component>
