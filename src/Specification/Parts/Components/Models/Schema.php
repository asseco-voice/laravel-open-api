<?php

namespace Voice\OpenApi\Specification\Parts\Components\Models;

use Voice\OpenApi\Exceptions\OpenApiException;
use Voice\OpenApi\Specification\Parts\Models\Properties;

class Schema implements Component
{
    public string $name;
    public string $type = 'object';
    public string $model;
    protected array $properties = [];

    /**
     * Set to true to reference a model within a component schema
     */
    public bool $referenced = false;

    /**
     * Set to true to indicate an array of objects for referenced component schema
     */
    public bool $multiple = false;

    public function generateProperties($modelColumns): void
    {
        if (!$modelColumns) {
            return;
        }

        $properties = new Properties($modelColumns);

        $this->appendProperties($properties);
    }

    protected function appendProperties(Properties $properties): void
    {
        $this->properties = $properties->toSchema();
    }

    public function toSchema(): array
    {
        if ($this->referenced) {
            return $this->referencedSchema();
        }

        return $this->standardSchema();
    }

    protected function referencedSchema(): array
    {
        if (!$this->model) {
            throw new OpenApiException("Referenced schema must have a model.");
        }

        $referencedModel = ['$ref' => "#/components/schemas/$this->model"];

        return $this->multiple ? $this->generateMultipleSchema($referencedModel) : $referencedModel;
    }

    protected function standardSchema(): array
    {
        $schema = array_merge(
            ['type' => $this->type],
            $this->properties,
        );

        $standardSchema = $this->multiple ? $this->generateMultipleSchema($schema) : $schema;

        return $this->name ? [$this->name => $standardSchema] : $standardSchema;
    }

    protected function generateMultipleSchema(array $items): array
    {
        return [
            'type'  => 'array',
            'items' => $items
        ];
    }
}
