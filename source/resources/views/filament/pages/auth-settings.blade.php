<x-filament::page xmlns:x-filament="http://www.w3.org/1999/html">
    <form wire:submit.prevent='submit'>
        {{ $this->form }}

        <div class='flex flex-wrap items-center gap-4 justify-start'>
            <x-filament::button type='submit'>
                Save
            </x-filament::button>
        </div>
    </form>
</x-filament::page>
