<?php

namespace MichaelNabil230\CrudGenerator;

use MichaelNabil230\CrudGenerator\Commands\CrudCommand;
use MichaelNabil230\CrudGenerator\Commands\RollBackCrudCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class CrudGeneratorServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('crud-generator')
            ->hasConfigFile()
            ->hasCommands([
                CrudCommand::class,
                RollBackCrudCommand::class,
            ]);
    }
}
