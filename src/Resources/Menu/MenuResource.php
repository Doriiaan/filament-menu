<?php

declare(strict_types=1);

namespace Doriiaan\FilamentMenu\Resources\Menu;

use Doriiaan\FilamentMenu\FilamentMenuPlugin;
use Doriiaan\FilamentMenu\Models\Menu;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class MenuResource extends Resource
{
    protected static ?string $model = Menu::class;

    /**
     * @return class-string<Model>
     */
    public static function getModel(): string
    {
        return FilamentMenuPlugin::get()->getMenuModel();
    }

    public static function getNavigationLabel(): string
    {
        return FilamentMenuPlugin::get()->getNavigationLabel() ?? Str::title(static::getPluralModelLabel()) ?? Str::title(static::getModelLabel());
    }

    public static function getNavigationIcon(): string
    {
        return FilamentMenuPlugin::get()->getNavigationIcon();
    }

    public static function getNavigationSort(): ?int
    {
        return FilamentMenuPlugin::get()->getNavigationSort();
    }

    public static function getNavigationGroup(): ?string
    {
        return FilamentMenuPlugin::get()->getNavigationGroup();
    }

    public static function getNavigationBadge(): ?string
    {
        /** @phpstan-ignore-next-line */
        return FilamentMenuPlugin::get()->getNavigationCountBadge() ? number_format(static::getModel()::count()) : null;
    }

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return Schemas\MenuForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return Tables\MenusTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMenus::route('/'),
            'edit' => Pages\EditMenu::route('/{record}/edit'),
        ];
    }
}
