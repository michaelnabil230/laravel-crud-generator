<?php

namespace MichaelNabil230\CrudGenerator\Models;

use MichaelNabil230\CrudGenerator\Models\Accessories\Generator;

class Controller extends Generator
{
    protected function __construct(
        string $name,
        protected object $model,
        protected Fields $fields,
    ) {
        parent::__construct($name);
    }

    protected function getStub(): string
    {
        return __DIR__ . '/../stubs/controller.stub';
    }

    public function showPageStatus(string $name, bool $statusDefault): bool
    {
        $show = (array) optional($this->methods)->show;

        return data_get($show, $name, $statusDefault);
    }

    protected function buildClass()
    {
        $replace = [];

        $replace = $this
            ->createMethods($replace);

        // $replace = [
        //     '{{viewName}}' => $viewName,
        //     '{{viewPath}}' => $viewPath,
        //     '{{modelVariableSingular}}' => $crudNameSingular,
        //     '{{modelVariablePlural}}' => $crudNamePlural,
        //     '{{routePrefix}}' => $routePrefix,
        //     '{{routePrefixCap}}' => $routePrefixCap,
        //     '{{routeGroup}}' => $routeGroup,
        // ];

        // $replace = $this->buildModelReplacements($replace, $modelClass);
        // $replace = $this->buildFormRequestReplacements($replace, $storeRequestClass, $updateRequestClass);
        // $replace = $this->buildFileSnippetReplacements($replace, $fields);

        return str_replace(
            array_keys($replace),
            array_values($replace),
            parent::buildClass()
        );
    }

    protected function createMethods(array $replace)
    {
        $methods = '';

        return array_merge($replace, [
            '{{methods}}' => $methods,
        ]);
    }

    protected function buildModelReplacements(array $replace, $modelClass)
    {
        return array_merge($replace, [
            'DummyFullModelClass' => $modelClass,
            '{{ namespacedModel }}' => $modelClass,
            '{{namespacedModel}}' => $modelClass,
            'DummyModelClass' => class_basename($modelClass),
            '{{ model }}' => class_basename($modelClass),
            '{{model}}' => class_basename($modelClass),
            'DummyModelVariable' => lcfirst(class_basename($modelClass)),
            '{{ modelVariable }}' => lcfirst(class_basename($modelClass)),
            '{{modelVariable}}' => lcfirst(class_basename($modelClass)),
        ]);
    }

    protected function buildFileSnippetReplacements($replace, $fields)
    {
        $snippet = <<<'EOD'
        if ($request->hasFile('{{fieldName}}')) {
            $requestData['{{fieldName}}'] = $request->file('{{fieldName}}')->store('uploads', 'public');
        }
        EOD;

        $fieldsArray = explode(';', $fields);
        $fileSnippet = '';

        if ($fields) {
            foreach ($fieldsArray as $index => $item) {
                $itemArray = explode('#', $item);
                if (trim($itemArray[1]) == 'file') {
                    $fileSnippet .= str_replace('{{fieldName}}', trim($itemArray[0]), $snippet) . "\n";
                }
            }
        }

        return array_merge($replace, [
            '{{fileSnippet}}' => $fileSnippet,
        ]);
    }

    protected function buildFormRequestReplacements(array $replace, string $storeRequestClass, string $updateRequestClass): array
    {
        $namespace = 'App\\Http\\Requests';

        $namespacedRequests = $namespace . '\\' . $storeRequestClass . ';';

        if ($storeRequestClass !== $updateRequestClass) {
            $namespacedRequests .= PHP_EOL . 'use ' . $namespace . '\\' . $updateRequestClass . ';';
        }

        return array_merge($replace, [
            '{{ storeRequest }}' => $storeRequestClass,
            '{{storeRequest}}' => $storeRequestClass,
            '{{ updateRequest }}' => $updateRequestClass,
            '{{updateRequest}}' => $updateRequestClass,
            '{{ namespacedStoreRequest }}' => $namespace . '\\' . $storeRequestClass,
            '{{namespacedStoreRequest}}' => $namespace . '\\' . $storeRequestClass,
            '{{ namespacedUpdateRequest }}' => $namespace . '\\' . $updateRequestClass,
            '{{namespacedUpdateRequest}}' => $namespace . '\\' . $updateRequestClass,
            '{{ namespacedRequests }}' => $namespacedRequests,
            '{{namespacedRequests}}' => $namespacedRequests,
        ]);
    }
}
