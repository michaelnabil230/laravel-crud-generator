<?php

namespace MichaelNabil230\CrudGenerator\Models;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class Views
{
    protected string $stubViewDirectoryPath;

    protected string $viewDirectoryPath;

    protected array $typeLookup = [
        'string' => 'text',
        'char' => 'text',
        'varchar' => 'text',
        'text' => 'textarea',
        'mediumtext' => 'textarea',
        'longtext' => 'textarea',
        'json' => 'textarea',
        'jsonb' => 'textarea',
        'binary' => 'textarea',
        'password' => 'password',
        'email' => 'email',
        'number' => 'number',
        'integer' => 'number',
        'bigint' => 'number',
        'mediumint' => 'number',
        'tinyint' => 'number',
        'smallint' => 'number',
        'decimal' => 'number',
        'double' => 'number',
        'float' => 'number',
        'date' => 'date',
        'datetime' => 'datetime-local',
        'timestamp' => 'datetime-local',
        'time' => 'time',
        'radio' => 'radio',
        'boolean' => 'radio',
        'enum' => 'select',
        'select' => 'select',
        'file' => 'file',
    ];

    protected function __construct(
        protected string $name,
        protected object $views,
        protected Fields $fields,
        protected object $methods,
    ) {
        $this->stubViewDirectoryPath = config('crud-generator.custom_template')
            ? config('crud-generator.path').'views/'
            : __DIR__.'/../stubs/views/';

        $this->viewDirectoryPath = $this->makeDirectory();
    }

    protected function viewPath(): string
    {
        $resource = Str::snake(Str::plural($this->name), '-');

        $views = config('view.paths')[0] ?? resource_path('views');

        return $views.DIRECTORY_SEPARATOR.$this->views->namespace.DIRECTORY_SEPARATOR.$resource.DIRECTORY_SEPARATOR;
    }

    protected function makeDirectory(): string
    {
        $directory = $this->viewPath();

        if (! File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        return $directory;
    }

    public static function make(
        string $name,
        object $views,
        Fields $fields,
        object $methods,
    ) {
        return new self(
            $name,
            $views,
            $fields,
            $methods,
        );
    }

    public function run()
    {
        return $this
            ->createIndex()
            ->createShow()
            ->createCreate()
            ->createEdit();
    }

    protected function createIndex()
    {
        $this->createView(
            'index',
            $this->getIndexContent()
        );

        return $this;
    }

    protected function getIndexContent(): string
    {
        $html = '';

        $this->fields->getByShow('show', true);

        return $html;
    }

    protected function createShow()
    {
        return $this->createView(
            'show',
            $this->getShowContent()
        );
    }

    protected function getShowContent(): string
    {
        $html = '';

        $this->fields->getByShow('show', true);

        return $html;
    }

    protected function createCreate()
    {
        $this->createView(
            'create',
            $this->getCreateContent()
        );

        return $this;
    }

    protected function getCreateContent(): string
    {
        $html = '';

        $this->fields->getByShow('create', true);

        return $html;
    }

    protected function createEdit()
    {
        return $this->createView(
            'edit',
            $this->getEditContent()
        );
    }

    protected function getEditContent(): string
    {
        $html = 'HI THIS IS EDIT CONTENT';

        $this->fields->getByShow('edit', true);

        return $html;
    }

    public function showPageStatus(string $name, bool $statusDefault): bool
    {
        $show = (array) optional($this->methods)->show;

        return data_get($show, $name, $statusDefault);
    }

    protected function createView(string $name, string $content)
    {
        if ($this->showPageStatus($name, true)) {
            $file = $this->getFilePath($name);
            File::put($file, $content);
        }

        return $this;
    }

    protected function getFilePath(string $name): string
    {
        return $this->viewDirectoryPath.$name.'.blade.php';
    }

    protected function wrapField(array $item, string $field): string
    {
        $formGroup = File::get($this->stubViewDirectoryPath.'form-fields/wrap-field.blade.stub');

        $labelText = "'".ucwords(strtolower(str_replace('_', ' ', $item['name'])))."'";

        if (true) {
            $labelText = 'trans(\''.$this->crudName.'.'.$item['name'].'\')';
        }

        return sprintf($formGroup, $item['name'], $labelText, $field);
    }

    protected function createField(array $item): string
    {
        switch ($this->typeLookup[$item['type']]) {
            case 'password':
                return $this->createPasswordField($item);
            case 'datetime-local':
            case 'time':
                return $this->createInputField($item);
            case 'radio':
                return $this->createRadioField($item);
            case 'textarea':
                return $this->createTextareaField($item);
            case 'select':
            case 'enum':
                return $this->createSelectField($item);
            default: // text
                return $this->createFormField($item);
        }
    }

    protected function createFormField(array $item): string
    {
        $start = $this->delimiter[0];
        $end = $this->delimiter[1];

        $required = $item['required'] ? 'required' : '';

        $markup = File::get($this->stubViewDirectoryPath.'form-fields/form-field.blade.stub');
        $markup = str_replace($start.'required'.$end, $required, $markup);
        $markup = str_replace($start.'fieldType'.$end, $this->typeLookup[$item['type']], $markup);
        $markup = str_replace($start.'itemName'.$end, $item['name'], $markup);
        $markup = str_replace($start.'crudNameSingular'.$end, $this->crudNameSingular, $markup);

        return $this->wrapField($item, $markup);
    }

    protected function createPasswordField(array $item): string
    {
        $start = $this->delimiter[0];
        $end = $this->delimiter[1];

        $required = $item['required'] ? 'required' : '';

        $markup = File::get($this->stubViewDirectoryPath.'form-fields/password-field.blade.stub');
        $markup = str_replace($start.'required'.$end, $required, $markup);
        $markup = str_replace($start.'itemName'.$end, $item['name'], $markup);
        $markup = str_replace($start.'crudNameSingular'.$end, $this->crudNameSingular, $markup);

        return $this->wrapField($item, $markup);
    }

    protected function createInputField(array $item): string
    {
        $start = $this->delimiter[0];
        $end = $this->delimiter[1];

        $required = $item['required'] ? 'required' : '';

        $markup = File::get($this->stubViewDirectoryPath.'form-fields/input-field.blade.stub');
        $markup = str_replace($start.'required'.$end, $required, $markup);
        $markup = str_replace($start.'fieldType'.$end, $this->typeLookup[$item['type']], $markup);
        $markup = str_replace($start.'itemName'.$end, $item['name'], $markup);
        $markup = str_replace($start.'crudNameSingular'.$end, $this->crudNameSingular, $markup);

        return $this->wrapField($item, $markup);
    }

    protected function createRadioField(array $item): string
    {
        $start = $this->delimiter[0];
        $end = $this->delimiter[1];

        $markup = File::get($this->stubViewDirectoryPath.'form-fields/radio-field.blade.stub');
        $markup = str_replace($start.'itemName'.$end, $item['name'], $markup);
        $markup = str_replace($start.'crudNameSingular'.$end, $this->crudNameSingular, $markup);

        return $this->wrapField($item, $markup);
    }

    protected function createTextareaField(array $item): string
    {
        $start = $this->delimiter[0];
        $end = $this->delimiter[1];

        $required = $item['required'] ? 'required' : '';

        $markup = File::get($this->stubViewDirectoryPath.'form-fields/textarea-field.blade.stub');
        $markup = str_replace($start.'required'.$end, $required, $markup);
        $markup = str_replace($start.'fieldType'.$end, $this->typeLookup[$item['type']], $markup);
        $markup = str_replace($start.'itemName'.$end, $item['name'], $markup);
        $markup = str_replace($start.'crudNameSingular'.$end, $this->crudNameSingular, $markup);

        return $this->wrapField($item, $markup);
    }

    protected function createSelectField(array $item): string
    {
        $start = $this->delimiter[0];
        $end = $this->delimiter[1];

        $required = $item['required'] ? 'required' : '';

        $markup = File::get($this->stubViewDirectoryPath.'form-fields/select-field.blade.stub');
        $markup = str_replace($start.'required'.$end, $required, $markup);
        $markup = str_replace($start.'options'.$end, $item['options'], $markup);
        $markup = str_replace($start.'itemName'.$end, $item['name'], $markup);
        $markup = str_replace($start.'crudNameSingular'.$end, $this->crudNameSingular, $markup);

        return $this->wrapField($item, $markup);
    }
}
