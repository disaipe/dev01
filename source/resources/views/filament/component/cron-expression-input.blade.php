<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
    xmlns:x-filament="http://www.w3.org/1999/html">
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
            state: $wire.{{ $applyStateBindingModifiers("\$entangle('{$getStatePath()}')") }},
            description: null,
            selectedPart: 0,
            valid: true,
            parseCron,
            selectPart
        }"

        x-init="$nextTick(() => parseCron())"
    >
        <script src="https://unpkg.com/cronstrue@latest/dist/cronstrue.min.js"></script>
        <script src="https://unpkg.com/cronstrue@latest/locales/{{ $locale }}.js"></script>

        <div class="flex space-x-4">
            <div class="flex flex-col">
                <x-filament::input.wrapper
                    :disabled="$isDisabled"
                    placeholder=""
                >
                    <x-filament::input
                        x-ref="input"
                        type="text"
                        x-model="state"
                        x-on:input="parseCron"
                        x-on:keyup="selectPart"
                        x-on:mouseup="selectPart"
                        style="word-spacing: 12px"
                        class="text-center"
                        :placeholder="$getPlaceholder()"
                    />
                </x-filament::input.wrapper>
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
