<?php

namespace MichaelNabil230\CrudGenerator\Models;

use Illuminate\Support\Str;
use MichaelNabil230\CrudGenerator\Models\Accessories\Generator;

class Request extends Generator
{
    protected function __construct(
        string $name,
        protected string $validations,
        protected string $modelClass,
    ) {
        parent::__construct($name);
    }

    protected function buildClass()
    {
        $replace = [];

        $replace = $this->replaceRules($replace);
        $replace = $this->replaceResourceId($replace);

        return str_replace(
            array_keys($replace),
            array_values($replace),
            parent::buildClass()
        );
    }

    protected function replaceRules(array $replace): array
    {
        return array_merge($replace, [
            '{{rules}}' => $this->validations,
            '{{ rules }}' => $this->validations,
        ]);
    }

    protected function replaceResourceId(array $replace): array
    {
        $nameBindingRoute = Str::camel($this->modelClass);

        $resourceId = '$this->'.$nameBindingRoute;

        return array_merge($replace, [
            '{{resourceId}}' => $resourceId,
            '{{ resourceId }}' => $resourceId,
        ]);
    }

    protected function getStub(): string
    {
        return config('crud-generator.custom_template')
            ? config('crud-generator.path').'/request.stub'
            : __DIR__.'/../stubs/request.stub';
    }

    protected function getDefaultNamespace(string $rootNamespace): string
    {
        return $rootNamespace.'\Http\Requests';
    }
}
