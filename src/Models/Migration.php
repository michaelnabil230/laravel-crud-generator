<?php

namespace MichaelNabil230\CrudGenerator\Models;

use Illuminate\Support\Str;
use MichaelNabil230\CrudGenerator\Models\Accessories\Generator;

class Migration extends Generator
{
    protected array $typeLookup = [
        'char' => 'char',
        'date' => 'date',
        'datetime' => 'dateTime',
        'time' => 'time',
        'timestamp' => 'timestamp',
        'text' => 'text',
        'mediumtext' => 'mediumText',
        'longtext' => 'longText',
        'json' => 'json',
        'jsonb' => 'jsonb',
        'binary' => 'binary',
        'number' => 'integer',
        'integer' => 'integer',
        'bigint' => 'bigInteger',
        'mediumint' => 'mediumInteger',
        'tinyint' => 'tinyInteger',
        'smallint' => 'smallInteger',
        'boolean' => 'boolean',
        'decimal' => 'decimal',
        'double' => 'double',
        'float' => 'float',
        'enum' => 'enum',
        'foreignId' => 'foreignIdFor',
    ];

    protected function __construct(
        string $name,
        protected Fields $fields,
    ) {
        parent::__construct($name);
    }

    protected function buildClass()
    {
        $schemaFields = $this->getMigration(
            fields: $this->fields->fields(),
        );

        if ($this->softDeletes ?? false) {
            $this->buildColumn($schemaFields, 'softDeletes');
        }

        $replace = [
            '{{schemaFields}}' => $schemaFields,
            '{{ schemaFields }}' => $schemaFields,
            '{{tableName}}' => $this->tableName(),
            '{{ tableName }}' => $this->tableName(),
        ];

        return str_replace(
            array_keys($replace),
            array_values($replace),
            parent::buildClass()
        );
    }

    protected function getMigration(object $fields): string
    {
        $typeLookup = $this->typeLookup;

        $schema = '';

        foreach ($fields as $field) {
            $fieldName = $field->name;
            $type = Str::camel($field->type) ?? 'string';
            $accessories = '';
            $typeTable = 'string';
            $options = '';

            if (!in_array($type, array_keys($typeLookup))) {
                continue;
            }

            $typeTable = $typeLookup[$type];

            if (in_array($type, ['select', 'enum'])) {
                [$options] = $this->select($field);
            } elseif ($type === 'foreignId') {
                [$typeTable, $fieldName, $options, $accessories] = $this->relationship($field);
            }

            $this->buildColumn($schema, $typeTable, $fieldName, $options, $accessories);
        }

        return $schema;
    }

    private function select(object $field): array
    {
        if (!property_exists($field, 'options')) {
            throw new \Exception('Options are not supported for select or enum fields.');
        }

        $enumOptionsStr = implode("','", (array)$field->options);
        $options = ", ['$enumOptionsStr']";

        return [
            $options,
        ];
    }

    private function relationship(object $field): array
    {
        if (!property_exists($field, 'relationship')) {
            throw new \Exception('Relationship is not supported for foreignId fields.');
        }

        $fieldName = $field->name;
        $typeTable = 'foreignIdFor';
        $options = ",'$fieldName'";
        $fieldName = $field->relationship->class;
        $table = $field->relationship->table;
        $table = $table ? "'$table'" : '';

        $accessories = "->constrained($table)";

        if (property_exists($field->relationship, 'cascade')) {
            foreach ($field->relationship->cascade as $cascade) {
                if (in_array($cascade, ['cascadeOnUpdate', 'restrictOnUpdate', 'cascadeOnDelete', 'restrictOnDelete', 'nullOnDelete'])) {
                    $accessories .= "->$cascade()";
                }
            }
        }

        return [
            $typeTable,
            $fieldName,
            $options,
            $accessories
        ];
    }

    private function buildColumn(&$schema, $typeTable, $fieldName = '', $options = '', $accessories = ''): void
    {
        if (!empty($fieldName)) {
            $fieldName = "'$fieldName'";
        }

        $column = $fieldName . $options;

        $tabIndent = '    ';
        $schema .= $this->buildBluePrint($typeTable, $column, $accessories);
        $schema .= "\n" . $tabIndent . $tabIndent . $tabIndent;
    }

    private function buildBluePrint(string $typeTable, string $column, string $accessories): string
    {
        return "\$table->$typeTable($column)$accessories;";
    }

    protected function getStub(): string
    {
        return config('crud-generator.custom_template')
            ? config('crud-generator.path') . '/migration.stub'
            : __DIR__ . '/../stubs/migration.stub';
    }

    protected function getPath(string $name): string
    {
        $tableName = $this->tableName();
        $datePrefix = date('Y_m_d_His');

        return database_path('/migrations/' . $datePrefix . '_create_' . $tableName . '_table.php');
    }

    public function tableName()
    {
        return Str::plural(Str::snake($this->name, '_'));
    }
}
