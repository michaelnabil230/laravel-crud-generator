<?php

namespace MichaelNabil230\CrudGenerator\Models;

use MichaelNabil230\CrudGenerator\Models\Accessories\Generator;

class Model extends Generator
{
    protected function __construct(
        string $name,
        protected object $model,
        protected Fields $fields,
    ) {
        parent::__construct($name);
    }

    protected function buildClass()
    {
        $table = $this->model->table ?: $this->class();
        $fillable = $this->fields->getFillable();

        $replace = [
            '{{table}}' => $table,
            '{{fillable}}' => $fillable,
            '{{relationships}}' => '',
        ];

        $replace = $this->buildSoftDelete($replace);
        // $replace = $this->buildRelationships($replace);

        return str_replace(
            array_keys($replace),
            array_values($replace),
            parent::buildClass()
        );
    }

    protected function getStub(): string
    {
        return config('crud-generator.custom_template')
            ? config('crud-generator.path').'/model.stub'
            : __DIR__.'/../stubs/model.stub';
    }

    protected function buildSoftDelete(array $replace): array
    {
        $softDelete = $this->softDeletes ?? false;

        return array_merge($replace, [
            '{{softDeletes}}' => $softDelete ? "use SoftDeletes;\n    " : '',
            '{{useSoftDeletes}}' => $softDelete ? "use Illuminate\Database\Eloquent\SoftDeletes;\n" : '',
        ]);
    }

    protected function getDefaultNamespace(string $rootNamespace): string
    {
        return is_dir(app_path('Models')) ? $rootNamespace.'\\Models' : $rootNamespace;
    }
}
