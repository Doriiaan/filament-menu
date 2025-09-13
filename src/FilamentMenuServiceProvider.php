<?php

namespace Doriiaan\FilamentMenu;

use Doriiaan\FilamentMenu\Livewire\CreateCustomText;
use Doriiaan\FilamentMenu\Livewire\MenuItems;
use Doriiaan\FilamentMenu\Livewire\MenuPanel;
use Filament\Support\Assets\AlpineComponent;
use Filament\Support\Assets\Asset;
use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use Livewire\Livewire;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilamentMenuServiceProvider extends PackageServiceProvider
{
    public static string $name = 'filament-menu';

    public static string $viewNamespace = 'filament-menu';

    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package->name(static::$name)
            ->hasInstallCommand(function (InstallCommand $command) {
                $command
                    ->publishConfigFile()
                    ->publishMigrations()
                    ->askToRunMigrations()
                    ->askToStarRepoOnGitHub('doriiaan/filament-menu');
            });

        $configFileName = $package->shortName();

        if (file_exists($package->basePath("/../config/{$configFileName}.php"))) {
            $package->hasConfigFile();
        }

        if (file_exists($package->basePath('/../database/migrations'))) {
            $package->hasMigrations($this->getMigrations());
        }

        if (file_exists($package->basePath('/../resources/lang'))) {
            $package->hasTranslations();
        }

        if (file_exists($package->basePath('/../resources/views'))) {
            $package->hasViews(static::$viewNamespace);
        }
    }

    public function packageRegistered(): void {}

    public function packageBooted(): void
    {
        // Asset Registration
        FilamentAsset::register(
            $this->getAssets(),
            $this->getAssetPackageName()
        );

        Livewire::component('menu-items', MenuItems::class);
        Livewire::component('menu-panel', MenuPanel::class);
        Livewire::component('create-custom-text', CreateCustomText::class);
    }

    protected function getAssetPackageName(): ?string
    {
        return 'doriiaan/filament-menu';
    }

    /**
     * @return array<Asset>
     */
    protected function getAssets(): array
    {
        return [
            AlpineComponent::make('filament-menu', __DIR__ . '/../resources/dist/filament-menu.js'),
            Css::make('filament-menu-styles', __DIR__ . '/../resources/dist/filament-menu.css'),
        ];
    }

    /**
     * @return array<string>
     */
    protected function getMigrations(): array
    {
        return [
            'create_menus_table',
        ];
    }
}
