<?php

declare(strict_types=1);

namespace Voice\OpenApi\Specification\Shared;

use Voice\OpenApi\Contracts\Serializable;

abstract class Schema implements Serializable
{
    public string $name;
    public string $type = 'object';
    public array $properties = [];

    /**
     * Set to true to indicate an array of objects.
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

    protected function generateMultipleSchema(array $items): array
    {
        return [
            'type'  => 'array',
            'items' => $items,
        ];
    }
}
