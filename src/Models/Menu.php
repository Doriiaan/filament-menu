<?php

declare(strict_types=1);

namespace Doriiaan\FilamentMenu\Models;

use Doriiaan\FilamentMenu\FilamentMenuPlugin;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property bool $is_visible
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\Doriiaan\FilamentMenu\Models\MenuItem[] $menuItems
 * @property-read int|null $menuItems_count
 */
class Menu extends Model
{
    protected $guarded = [];

    public function getTable(): string
    {
        return config('filament-menu.tables.menus', parent::getTable());
    }

    protected function casts(): array
    {
        return [
            'is_visible' => 'bool',
        ];
    }

    public function menuItems(): HasMany
    {
        /** @phpstan-ignore-next-line */
        return $this->hasMany(FilamentMenuPlugin::get()->getMenuItemModel())
            ->whereNull('parent_id')
            ->with('children')
            ->orderBy('parent_id')
            ->orderBy('order');
    }
}
