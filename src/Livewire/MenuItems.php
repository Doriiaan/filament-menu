<?php

declare(strict_types=1);

namespace Doriiaan\FilamentMenu\Livewire;

use Doriiaan\FilamentMenu\Concerns\ManagesMenuItemHierarchy;
use Doriiaan\FilamentMenu\FilamentMenuPlugin;
use Doriiaan\FilamentMenu\Models\Menu;
use Doriiaan\FilamentMenu\Models\MenuItem;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Support\Enums\Size;
use Filament\Support\Enums\Width;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class MenuItems extends Component implements HasActions, HasSchemas
{
    use InteractsWithActions;
    use InteractsWithSchemas;
    use ManagesMenuItemHierarchy;

    public Menu $menu;

    protected Collection $indexed;

    public function booted()
    {
        $this->indexed = FilamentMenuPlugin::get()
            ->getMenuItemModel()::query()
            ->with('linkable')
            ->orderBy('order')
            ->get()
            ->keyBy('id');
    }

    #[Computed]
    #[On('menu:created')]
    public function menuItems(): Collection
    {
        $menuItems = $this->menu->menuItems;
        self::addPathToItems($menuItems);

        return $menuItems;
    }

    public static function addPathToItems(Collection $menuItems, ?string $parentPath = null)
    {
        $idx = 1;
        foreach ($menuItems as &$menuItem) {
            if ($parentPath === null) {
                $menuItem->path = (string) $idx;
            } else {
                $menuItem->path = $parentPath . '.' . $idx;
            }

            self::addPathToItems($menuItem->children, $menuItem->path);
            $idx++;
        }
    }

    public function reorder(array $order, ?string $parentId = null): void
    {
        $this->getMenuItemService()->updateOrder($order, $parentId);
    }

    public function reorderAction(): Action
    {
        return Action::make('reorder')
            ->label(__('filament-forms::components.builder.actions.reorder.label'))
            ->icon(FilamentIcon::resolve('forms::components.builder.actions.reorder') ?? 'heroicon-m-arrows-up-down')
            ->color('gray')
            ->iconButton()
            ->extraAttributes(['data-sortable-handle' => true, 'class' => 'cursor-move'])
            ->livewireClickHandlerEnabled(false)
            ->size(Size::Small);
    }

    public function indent(int $itemId): void
    {
        /** @var MenuItem */
        $item = $this->indexed->get($itemId);

        if (! $item) {
            return;
        }

        $previousSibling = FilamentMenuPlugin::get()->getMenuItemModel()::query()
            ->where('menu_id', $item->menu_id)
            ->where('parent_id', $item->parent_id)
            ->where('order', '<', $item->order)
            ->orderByDesc('order')
            ->first();

        if (! $previousSibling) {
            return;
        }

        $maxOrder = FilamentMenuPlugin::get()->getMenuItemModel()::query()
            ->where('parent_id', $previousSibling->id)
            ->max('order') ?? 0;

        $item->update([
            'parent_id' => $previousSibling->id,
            'order' => $maxOrder + 1,
        ]);

        $this->reorderSiblings($item->getOriginal('parent_id'));
    }

    public function unindent(int $itemId): void
    {
        /** @var MenuItem */
        $item = $this->indexed->get($itemId);

        if (! $item || ! $item->parent_id) {
            return;
        }

        $parent = $item->parent;
        if (! $parent) {
            return;
        }

        $maxOrder = FilamentMenuPlugin::get()->getMenuItemModel()::query()
            ->where('menu_id', $item->menu_id)
            ->where('parent_id', $parent->parent_id)
            ->max('order') ?? 0;

        $oldParentId = $item->parent_id;

        $item->update([
            'parent_id' => $parent->parent_id,
            'order' => $maxOrder + 1,
        ]);

        $this->reorderSiblings($oldParentId);

        $this->indexed->put($item->id, $item->fresh());
    }

    private function reorderSiblings(?int $parentId): void
    {
        /** @var Collection<MenuItem> */
        $siblings = FilamentMenuPlugin::get()->getMenuItemModel()::query()
            ->where('parent_id', $parentId)
            ->orderBy('order')
            ->get();

        $siblings->each(function (MenuItem $sibling, int $index) {
            $sibling->update(['order' => $index + 1]);
        });
    }

    public function indentAction(): Action
    {
        return Action::make('indent')
            ->label(__('filament-menu::menu.actions.indent'))
            ->icon('heroicon-o-arrow-right')
            ->color('gray')
            ->iconButton()
            ->size(Size::Small)
            ->action(fn (array $arguments) => $this->indent($arguments['id']))
            ->visible(
                fn (array $arguments): bool => FilamentMenuPlugin::get()->isIndentActionsEnabled() &&
                    $this->canIndent($arguments['id']),
            );
    }

    public function unindentAction(): Action
    {
        return Action::make('unindent')
            ->label(__('filament-menu::menu.actions.unindent'))
            ->icon('heroicon-o-arrow-left')
            ->color('gray')
            ->iconButton()
            ->size(Size::Small)
            ->action(fn (array $arguments) => $this->unindent($arguments['id']))
            ->visible(
                fn (array $arguments): bool => FilamentMenuPlugin::get()->isIndentActionsEnabled() &&
                    $this->canUnindent($arguments['id']),
            );
    }

    public function canIndent(int $itemId): bool
    {
        /** @var MenuItem */
        $item = $item = $this->indexed->get($itemId);

        if (! $item) {
            return false;
        }

        return $this->indexed
            ->where('parent_id', $item->parent_id)
            ->where('order', '<', $item->order)
            ->isNotEmpty();
    }

    public function canUnindent(int $itemId): bool
    {
        return ($this->indexed[$itemId]->parent_id ?? null) !== null;
    }

    public function editAction(): Action
    {
        return Action::make('edit')
            ->label(__('filament-actions::edit.single.label'))
            ->iconButton()
            ->size(Size::Small)
            ->modalHeading(fn (array $arguments): string => __('filament-actions::edit.single.modal.heading', ['label' => $arguments['title']]))
            ->icon('heroicon-m-pencil-square')
            ->fillForm(fn (array $arguments): array => $this->getMenuItemService()->findByIdWithRelations($arguments['id'])->toArray())
            ->schema($this->getEditFormSchema())
            ->action(fn (array $data, array $arguments) => $this->getMenuItemService()->update($arguments['id'], $data))
            ->modalWidth(Width::Medium)
            ->modal()
            ->modalAutofocus(false)
            ->slideOver();
    }

    public function deleteAction(): Action
    {
        return Action::make('delete')
            ->label(__('filament-actions::delete.single.label'))
            ->color('danger')
            ->groupedIcon(FilamentIcon::resolve('actions::delete-action.grouped') ?? 'heroicon-m-trash')
            ->icon('heroicon-s-trash')
            ->iconButton()
            ->size(Size::Small)
            ->requiresConfirmation()
            ->modalHeading(fn (array $arguments): string => __('filament-actions::delete.single.modal.heading', ['label' => $arguments['title']]))
            ->modalSubmitActionLabel(__('filament-actions::delete.single.modal.actions.delete.label'))
            ->modalIcon(FilamentIcon::resolve('actions::delete-action.modal') ?? 'heroicon-o-trash')
            ->action(function (array $arguments): void {
                $this->getMenuItemService()->delete($arguments['id']);
            });
    }

    public function render(): View
    {
        return view('filament-menu::livewire.menu-items');
    }

    protected function getEditFormSchema(): array
    {
        return [
            TextInput::make('title')
                ->label(__('filament-menu::menu.form.title'))
                ->required(),
            TextInput::make('url')
                ->hidden(fn (?string $state, Get $get): bool => blank($state) || filled($get('linkable_type')))
                ->label(__('filament-menu::menu.form.url'))
                ->required(),
            TextInput::make('linkable_type')
                ->label(__('filament-menu::menu.form.linkable_type'))
                ->readOnly()
                ->hidden(fn (?string $state): bool => blank($state)),
            TextInput::make('linkable_id')
                ->label(__('filament-menu::menu.form.linkable_id'))
                ->hidden(fn (?string $state): bool => blank($state))
                ->readOnly(),
            Fieldset::make()
                ->visible(fn () => FilamentMenuPlugin::get()->getMenuItemFields() !== [])
                ->schema(FilamentMenuPlugin::get()->getMenuItemFields()),
        ];
    }
}
