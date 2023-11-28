<?php

namespace timolake\livewireForms;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use timolake\livewireForms\Commands\LivewireFormCommand;

class LivewireFormServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('livewireForm')
            ->hasConfigFile()
            ->hasViews();
        //            ->hasMigration('create_skeleton_table')
        //            ->hasCommand(LivewireFormCommand::class)
    }
}
