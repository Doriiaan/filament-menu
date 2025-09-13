<?php

declare(strict_types=1);

namespace Doriiaan\FilamentMenu\Resources\Menu\Pages;

use Doriiaan\FilamentMenu\FilamentMenuPlugin;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMenus extends ListRecords
{
    public static function getResource(): string
    {
        return FilamentMenuPlugin::get()->getResource();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
