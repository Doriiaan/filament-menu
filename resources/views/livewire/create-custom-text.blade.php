<form wire:submit="save">
    <x-filament::section
        :heading="__('filament-menu::menu.custom_text')"
        :collapsible="true"
        :persist-collapsed="true"
        id="create-custom-text"
    >
        {{ $this->form }}

        <x-slot:footer>
            <x-filament::button type="submit">
                {{ __('filament-menu::menu.actions.add.label') }}
            </x-filament::button>
        </x-slot:footer>
    </x-filament::section>
</form>
