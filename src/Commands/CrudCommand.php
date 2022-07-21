<?php

namespace MichaelNabil230\LaravelCrudGenerator\Commands;

use Illuminate\Database\Console\Migrations\BaseCommand;
use Illuminate\Support\Facades\Storage;
use MichaelNabil230\LaravelCrudGenerator\LaravelCrudGenerator;
use Symfony\Component\Console\Input\InputOption;

class CrudCommand extends BaseCommand
{
    public $signature = 'crud:generate';

    public $description = 'Generate CRUD for your model';

    public function handle(): int
    {
        $file = $this->argument('file');

        $fileFromDisk = Storage::disk('crud')->get($file);

        LaravelCrudGenerator::make($fileFromDisk)->run();

        $this->comment('All done');

        return self::SUCCESS;
    }

    protected function getOptions(): array
    {
        return [
            ['file', null, InputOption::VALUE_REQUIRED, 'Generate CRUD form json file.'],
        ];
    }
}
