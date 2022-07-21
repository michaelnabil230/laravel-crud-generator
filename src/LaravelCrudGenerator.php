<?php

namespace MichaelNabil230\LaravelCrudGenerator;

use MichaelNabil230\LaravelCrudGenerator\Models\Model;
use MichaelNabil230\LaravelCrudGenerator\Models\Views;
use MichaelNabil230\LaravelCrudGenerator\Models\Fields;
use MichaelNabil230\LaravelCrudGenerator\Models\Request;
use MichaelNabil230\LaravelCrudGenerator\Models\Controller;

class LaravelCrudGenerator
{
    protected object $data;
    protected Fields $fields;
    protected string $modelClass;

    public function __construct(string $file)
    {
        $this->data = json_decode($file);
    }

    public static function make($file)
    {
        return new self($file);
    }

    public function run()
    {
        return $this
            ->generateFields()
            ->generateModel()
            // ->generateViews()
            // ->generateMigration()
            // ->generateController()
            ->generateFormRequests();
    }

    public function data(): object
    {
        return $this->data;
    }

    public function generateFields(): self
    {
        $fields = data_get($this->data, 'fields', []);

        $this->fields = Fields::make($fields);

        return $this;
    }

    public function generateViews(): self
    {
        Views::make(
            name: $this->data->views->resource ?? $this->data->name,
            views: $this->data->views,
            fields: $this->fields,
            methods: $this->data->methods,
        )->run();

        return $this;
    }

    public function generateMigration(): self
    {
        return $this;
    }

    public function generateModel(): self
    {
        $model = Model::make(
            name: $this->data->name,
            model: $this->data->model,
            fields: $this->fields,
        );

        $this->modelClass = $model->class();

        $model->handle();

        return $this;
    }

    public function generateController(): self
    {
        Controller::make(
            name: $this->data->name,
            model: $this->data->model,
            fields: $this->fields,
        )->handle();

        return $this;
    }

    public function generateFormRequests(): array
    {
        $modelClass = $this->modelClass;

        $storeRequestClass = 'Store' . class_basename($modelClass) . 'Request';

        Request::make(
            name: $storeRequestClass,
            validations: $this->fields->getValidations('create'),
        )->handle();

        $updateRequestClass = 'Update' . class_basename($modelClass) . 'Request';

        Request::make(
            name: $updateRequestClass,
            validations: $this->fields->getValidations('update'),
        )->handle();

        return [$storeRequestClass, $updateRequestClass];
    }
}
