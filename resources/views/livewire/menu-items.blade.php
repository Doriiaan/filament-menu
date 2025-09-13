<div>
    @if($this->menuItems->isNotEmpty())
        <ul
            x-load
            x-load-src="{{ \Filament\Support\Facades\FilamentAsset::getAlpineComponentSrc('filament-menu', 'doriiaan/filament-menu') }}"
            x-data="menuBuilder({ parentId: 0 })"
            class="space-y-2"
        >
            @foreach($this->menuItems as $menuItem)
                <x-filament-menu::menu-item
                    :item="$menuItem"
                    :parent=true
                />
            @endforeach
        </ul>
    @else
        <filament-menu::components.empty-state
            icon="heroicon-o-document"
            :heading="trans('filament-menu::items.empty.heading')"
        />
    @endif

    <x-filament-actions::modals />
</div>
