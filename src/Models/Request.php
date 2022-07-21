<?php

namespace MichaelNabil230\LaravelCrudGenerator\Models;

use MichaelNabil230\LaravelCrudGenerator\Models\Accessories\Generator;

class Request extends Generator
{
    protected function __construct(
        string $name,
        protected string $validations,
    ) {
        parent::__construct($name);
    }

    protected function buildClass()
    {
        $replace = [
            '{{rules}}' => $this->validations,
        ];

        return str_replace(
            array_keys($replace),
            array_values($replace),
            parent::buildClass()
        );
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
