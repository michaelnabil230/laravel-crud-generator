<?php

namespace MichaelNabil230\LaravelCrudGenerator\Models\Accessories;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use InvalidArgumentException;

abstract class Generator
{
    protected string $name;

    /**
     * Reserved names that cannot be used for generation.
     *
     * @var string[]
     */
    protected $reservedNames = [
        '__halt_compiler',
        'abstract',
        'and',
        'array',
        'as',
        'break',
        'callable',
        'case',
        'catch',
        'class',
        'clone',
        'const',
        'continue',
        'declare',
        'default',
        'die',
        'do',
        'echo',
        'else',
        'elseif',
        'empty',
        'enddeclare',
        'endfor',
        'endforeach',
        'endif',
        'endswitch',
        'endwhile',
        'enum',
        'eval',
        'exit',
        'extends',
        'final',
        'finally',
        'fn',
        'for',
        'foreach',
        'function',
        'global',
        'goto',
        'if',
        'implements',
        'include',
        'include_once',
        'instanceof',
        'insteadof',
        'interface',
        'isset',
        'list',
        'match',
        'namespace',
        'new',
        'or',
        'print',
        'private',
        'protected',
        'public',
        'readonly',
        'require',
        'require_once',
        'return',
        'static',
        'switch',
        'throw',
        'trait',
        'try',
        'unset',
        'use',
        'var',
        'while',
        'xor',
        'yield',
        '__CLASS__',
        '__DIR__',
        '__FILE__',
        '__FUNCTION__',
        '__LINE__',
        '__METHOD__',
        '__NAMESPACE__',
        '__TRAIT__',
    ];

    public function __construct(string $name)
    {
        $this->files = new Filesystem();
        $this->name = $name;
    }

    public static function make(...$arguments): static
    {
        return new static(...$arguments);
    }

    abstract protected function getStub(): string;

    /**
     * @return bool|null
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function handle()
    {
        if ($this->isReservedName($this->name())) {
            throw new InvalidArgumentException('Reserved names are not allowed.');
        }

        $name = $this->class();

        $path = $this->getPath($name);

        $this->makeDirectory($path);

        $this->files->put($path, $this->sortImports($this->buildClass()));

        // $this->info($this->type . ' created successfully.');
    }

    protected function qualifyClass(string $name): string
    {
        $name = ltrim($name, '\\/');

        $name = str_replace('/', '\\', $name);

        $rootNamespace = $this->rootNamespace();

        if (Str::startsWith($name, $rootNamespace)) {
            return $name;
        }

        return $this->qualifyClass(
            $this->getDefaultNamespace(trim($rootNamespace, '\\')).'\\'.$name
        );
    }

    protected function qualifyModel(string $model): string
    {
        $model = ltrim($model, '\\/');

        $model = str_replace('/', '\\', $model);

        $rootNamespace = $this->rootNamespace();

        if (Str::startsWith($model, $rootNamespace)) {
            return $model;
        }

        return is_dir(app_path('Models'))
            ? $rootNamespace.'Models\\'.$model
            : $rootNamespace.$model;
    }

    protected function getDefaultNamespace(string $rootNamespace): string
    {
        return $rootNamespace;
    }

    protected function alreadyExists(string $rawName): bool
    {
        return $this->files->exists($this->getPath($this->qualifyClass($rawName)));
    }

    protected function getPath(string $name): string
    {
        $name = Str::replaceFirst($this->rootNamespace(), '', $name);

        return app_path().'/'.str_replace('\\', '/', $name).'.php';
    }

    protected function makeDirectory(string $path): string
    {
        if (! $this->files->isDirectory(dirname($path))) {
            $this->files->makeDirectory(dirname($path), 0777, true, true);
        }

        return $path;
    }

    /**
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function buildClass()
    {
        $stub = $this->files->get($this->getStub());

        return $this->replaceNamespace($stub)->replaceClass($stub);
    }

    protected function replaceNamespace(string &$stub): self
    {
        $name = $this->class();

        $searches = [
            ['DummyNamespace', 'DummyRootNamespace'],
            ['{{ namespace }}', '{{ rootNamespace }}'],
            ['{{namespace}}', '{{rootNamespace}}'],
        ];

        dd([$this->getNamespace($name), $this->rootNamespace()]);

        foreach ($searches as $search) {
            $stub = str_replace(
                $search,
                [$this->getNamespace($name), $this->rootNamespace()],
                $stub
            );
        }

        return $this;
    }

    protected function getNamespace(string $name): string
    {
        return trim(implode('\\', array_slice(explode('\\', $name), 0, -1)), '\\');
    }

    public function class(): string
    {
        $name = $this->qualifyClass($this->name());

        return str_replace($this->getNamespace($name).'\\', '', $name);
    }

    protected function replaceClass(string $stub): string
    {
        $class = $this->class();

        return str_replace(['DummyClass', '{{ class }}', '{{class}}'], $class, $stub);
    }

    protected function sortImports(string $stub): string
    {
        if (preg_match('/(?P<imports>(?:use [^;{]+;$\n?)+)/m', $stub, $match)) {
            $imports = explode("\n", trim($match['imports']));

            sort($imports);

            return str_replace(trim($match['imports']), implode("\n", $imports), $stub);
        }

        return $stub;
    }

    protected function name(): string
    {
        return trim($this->name);
    }

    protected function rootNamespace(): string
    {
        return app()->getNamespace();
    }

    protected function viewPath(string $path = ''): string
    {
        $views = config('view.paths')[0] ?? resource_path('views');

        return $views.($path ? DIRECTORY_SEPARATOR.$path : $path);
    }

    protected function isReservedName(string $name): bool
    {
        $name = strtolower($name);

        return in_array($name, $this->reservedNames);
    }
}
