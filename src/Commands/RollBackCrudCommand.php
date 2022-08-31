<?php

namespace MichaelNabil230\CrudGenerator\Commands;

use Illuminate\Console\ConfirmableTrait;
use Illuminate\Database\Console\Migrations\BaseCommand;
use Illuminate\Support\Facades\Storage;
use MichaelNabil230\CrudGenerator\CrudGenerator;
use Symfony\Component\Console\Input\InputOption;

class RollBackCrudCommand extends BaseCommand
{
    use ConfirmableTrait;

    public $signature = 'crud:rollback';

    public $description = 'Rollback the CRUD for your models';

    public function handle(): int
    {
        if (! $this->confirmToProceed()) {
            return 1;
        }

        $storage = Storage::disk('crud');

        if ($this->hasOption('file')) {
            $file = $this->option('file');
            $fileFromDisk = $storage->get($file);
            CrudGenerator::make($fileFromDisk)->run();
        } else {
            collect($storage->allFiles())->map(function ($file) use ($storage) {
                $fileFromDisk = $storage->get($file);
                CrudGenerator::make($fileFromDisk)->run();
            });
        }

        $this->comment('All done');

        return self::SUCCESS;
    }

    protected function getOptions(): array
    {
        return [
            ['file', null, InputOption::VALUE_OPTIONAL, 'Generate CRUD form json file.'],
            ['force', null, InputOption::VALUE_NONE, 'Force the operation to run when in production.'],
        ];
    }
}
