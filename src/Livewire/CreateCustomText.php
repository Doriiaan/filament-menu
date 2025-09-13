<?php

declare(strict_types=1);

namespace Doriiaan\FilamentMenu\Livewire;

use Doriiaan\FilamentMenu\Models\Menu;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class CreateCustomText extends Component implements HasForms
{
    use InteractsWithForms;

    public Menu $menu;

    public string $title = '';

    public function save(): void
    {
        $this->validate([
            'title' => ['required', 'string'],
        ]);

        $this->menu
            ->menuItems()
            ->create([
                'title' => $this->title,
                'order' => $this->menu->menuItems->max('order') + 1,
            ]);

        Notification::make()
            ->title(__('filament-menu::menu.notifications.created.title'))
            ->success()
            ->send();

        $this->reset('title');
        $this->dispatch('menu:created');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label(__('filament-menu::menu.form.title'))
                    ->required(),
            ]);
    }

    public function render(): View
    {
        return view('filament-menu::livewire.create-custom-text');
    }
}
