<?php

namespace MichaelNabil230\CrudGenerator\Tests;

use MichaelNabil230\CrudGenerator\CrudGeneratorServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected $consoleOutput;

    protected function setUp(): void
    {
        parent::setUp();

        exec('rm -rf ' . __DIR__ . '/temp/*');
        exec('rm -rf ' . app_path('/*'));
        exec('rm -rf ' . database_path('migrations/*'));
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->consoleOutput = '';
    }

    protected function getPackageProviders($app)
    {
        return [
            CrudGeneratorServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('view.paths', [__DIR__ . '/temp/views']);

        $app['config']->set('crud-generator', [
            'custom_template' => false,
            'path' => base_path('resources/crud-generator/'),
            'view_columns_number' => 3,
            'dynamic_view_template' => [
                'index' => ['formHeadingHtml', 'formBodyHtml', 'crudName', 'crudNameCap', 'modelName', 'viewName', 'routeGroup'],
                'form' => ['formFieldsHtml'],
                'create' => ['crudName', 'crudNameCap', 'modelName', 'modelNameCap', 'viewName', 'routeGroup', 'viewTemplateDir'],
                'edit' => ['crudName', 'crudNameSingular', 'crudNameCap', 'modelNameCap', 'modelName', 'viewName', 'routeGroup', 'viewTemplateDir'],
                'show' => ['formHeadingHtml', 'formBodyHtml', 'formBodyHtmlForShowView', 'crudName', 'crudNameSingular', 'crudNameCap', 'modelName', 'viewName', 'routeGroup'],
            ],
        ]);
    }

    public function consoleOutput()
    {
        return $this->consoleOutput ?: $this->consoleOutput = $this->app[\Illuminate\Contracts\Console\Kernel::class]->output();
    }
}
