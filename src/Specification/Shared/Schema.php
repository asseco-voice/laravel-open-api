<?php

namespace Voice\OpenApi\Specification\Shared;

use Voice\OpenApi\Contracts\Serializable;
use Voice\OpenApi\Exceptions\OpenApiException;

class Schema implements Serializable
{
    public string $name;
    public string $type = 'object';
    public array $properties = [];

    /**
     * Set to true to reference a model within a component schema
     */
    public bool $referenced = false;

    /**
     * Set to true to indicate an array of objects for referenced component schema
     */
    public bool $multiple = false;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function generateProperties($columns): self
    {
        if (!$columns) {
            return $this;
        }

        $properties = new Properties($columns);

        $this->appendProperties($properties);

        return $this;
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
        $referencedModel = ['$ref' => "#/components/schemas/$this->name"];

        return $this->multiple ? $this->generateMultipleSchema($referencedModel) : $referencedModel;
    }

    protected function standardSchema(): array
    {
        $schema = array_merge(
            ['type' => $this->type],
            $this->properties,
        );

        $standardSchema = $this->multiple ? $this->generateMultipleSchema($schema) : $schema;

        return isset($this->name) ? [$this->name => $standardSchema] : $standardSchema;
    }

    protected function generateMultipleSchema(array $items): array
    {
        return [
            'type'  => 'array',
            'items' => $items
        ];
    }
}
