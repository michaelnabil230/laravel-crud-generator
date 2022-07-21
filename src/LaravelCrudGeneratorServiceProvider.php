<?php

namespace MichaelNabil230\LaravelCrudGenerator;

use Spatie\LaravelPackageTools\Package;
use MichaelNabil230\LaravelCrudGenerator\Commands\CrudCommand;
use MichaelNabil230\LaravelCrudGenerator\Commands\RollBackCrudCommand;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelCrudGeneratorServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-crud-generator')
            ->hasConfigFile()
            ->hasViews()
            ->hasCommands([
                CrudCommand::class,
                RollBackCrudCommand::class
            ]);
    }
}
