<?php

declare(strict_types=1);

namespace Doriiaan\FilamentMenu\Resources\Menu\Pages;

use Doriiaan\FilamentMenu\FilamentMenuPlugin;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMenu extends EditRecord
{
    protected string $view = 'filament-menu::edit-record';

    public static function getResource(): string
    {
        return FilamentMenuPlugin::get()->getResource();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
