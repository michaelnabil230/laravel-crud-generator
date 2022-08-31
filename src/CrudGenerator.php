<?php

namespace MichaelNabil230\CrudGenerator;

use MichaelNabil230\CrudGenerator\Models\Model;
use MichaelNabil230\CrudGenerator\Models\Views;
use MichaelNabil230\CrudGenerator\Models\Fields;
use MichaelNabil230\CrudGenerator\Models\Request;
use MichaelNabil230\CrudGenerator\Models\Migration;
use MichaelNabil230\CrudGenerator\Models\Controller;

class CrudGenerator
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
            // ->generateModel()
            // ->generateViews()
            ->generateMigration();
            // ->generateController()
            // ->generateFormRequests();
    }

    protected function generateFields(): self
    {
        $fields = data_get($this->data, 'fields', []);

        $this->fields = Fields::make($fields);

        return $this;
    }

    protected function generateViews(): self
    {
        Views::make(
            name: $this->data->views->resource ?? $this->data->name,
            views: $this->data->views,
            fields: $this->fields,
            methods: $this->data->methods,
        )->run();

        return $this;
    }

    protected function generateMigration(): self
    {
        Migration::make(
            name: $this->data->name,
            fields: $this->fields,
        )->handle();

        return $this;
    }

    protected function generateModel(): self
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

    protected function generateController(): self
    {
        Controller::make(
            name: $this->data->name,
            model: $this->data->model,
            fields: $this->fields,
        )->handle();

        return $this;
    }

    protected function generateFormRequests(): array
    {
        $modelClass = $this->modelClass;

        $storeRequestClass = 'Store' . class_basename($modelClass) . 'Request';

        Request::make(
            name: $storeRequestClass,
            modelClass: $modelClass,
            validations: $this->fields->getValidations('create'),
        )->handle();

        $updateRequestClass = 'Update' . class_basename($modelClass) . 'Request';

        Request::make(
            name: $updateRequestClass,
            modelClass: $modelClass,
            validations: $this->fields->getValidations('update'),
        )->handle();

        return [$storeRequestClass, $updateRequestClass];
    }
}
