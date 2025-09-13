<x-filament-panels::page @class([
    'fi-resource-edit-record-page',
    'fi-resource-' . str_replace('/', '-', $this->getResource()::getSlug()),
    'fi-resource-record-' . $record->getKey(),
])>
    <x-filament::section>
        {{ $this->content }}
    </x-filament::section>



    <div class="grid grid-cols-12 gap-4" wire:ignore>
        <div class="flex flex-col col-span-12 gap-4 sm:col-span-4">
            @if (\Doriiaan\FilamentMenu\FilamentMenuPlugin::get()->isShowCustomTextPanel())
                <livewire:create-custom-text :menu="$record" />
            @endif

            @foreach (\Doriiaan\FilamentMenu\FilamentMenuPlugin::get()->getMenuPanels() as $menuPanel)
                <livewire:menu-panel :menu="$record" :menuPanel="$menuPanel" />
            @endforeach
        </div>
        <div class="col-span-12 sm:col-span-8">
            <x-filament::section>
                <livewire:menu-items :menu="$record" />
            </x-filament::section>
        </div>
    </div>

</x-filament-panels::page>
