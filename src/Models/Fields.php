<?php

namespace MichaelNabil230\LaravelCrudGenerator\Models;

use stdClass;

class Fields
{
    protected object $fields;

    public function __construct(
        array $fields
    ) {
        $this->fields = $this->format($fields);
    }

    public static function make(array $fields): self
    {
        return new self($fields);
    }

    public function format(array $fields): object
    {
        $object = new stdClass();
        foreach ($fields as $key => $value) {
            $object->$key = $value;
        }

        return $object;
    }

    public function getRelationships(): array
    {
        return collect($this->fields)->filter(function ($field) {
            return property_exists($field, 'relationships');
        })->toArray();
    }

    public function getByName(string $name)
    {
        return collect($this->fields)->first(function ($field) use ($name) {
            return property_exists($field, 'name') && $field->name === $name;
        });
    }

    public function getByShow(string $name, bool $value): array
    {
        return collect($this->fields)->filter(function ($field) use ($name, $value) {
            return property_exists($field, 'show') && property_exists($field->show, $name) && $field->show->$name === $value;
        })->toArray();
    }

    public function getFillable(): string
    {
        $fillable = collect($this->fields)->pluck('name')->toArray();

        return $this->convertArrayToString($fillable);
    }

    public function getValidations(string $type)
    {
        return collect($this->fields)
            ->mapWithKeys(function ($field) use ($type) {
                $normalValidations = [];
                if (property_exists($field, 'validations')) {
                    $normalValidations = $field->validations ?? [];
                }

                if (property_exists($field, 'validations_in_'.$type)) {
                    $relationshipValidations = $field->{'validations_in_'.$type} ?? [];
                    $normalValidations = array_merge($normalValidations, $relationshipValidations);
                }

                return [$field->name => $normalValidations];
            })
            ->filter()
            ->map(fn ($validations, $name) => "'$name' => ".json_encode($validations).',')
            ->implode("\n");
    }

    protected function convertArrayToString(array $data)
    {
        $commaSeparatedString = implode("', '", $data);

        return "['".$commaSeparatedString."']";
    }
}
