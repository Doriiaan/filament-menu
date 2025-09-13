<?php

namespace Doriiaan\FilamentMenu\Resources\Menu\Schemas;

use Doriiaan\FilamentMenu\FilamentMenuPlugin;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Schema;

class MenuForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Grid::make(4)
                    ->schema([
                        TextInput::make('name')
                            ->label(__('filament-menu::menu.resource.name.label'))
                            ->required()
                            ->columnSpanFull(),
                    ]),

                Group::make()
                    ->visible(fn () => FilamentMenuPlugin::get()->getMenuFields() !== [])
                    ->schema(FilamentMenuPlugin::get()->getMenuFields()),
            ]);
    }
}
