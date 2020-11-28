<?php

declare(strict_types=1);

namespace Asseco\OpenApi\Specification\Shared;

class StandardSchema extends Schema
{
    public function toSchema(): array
    {
        $schema = array_merge(
            ['type' => $this->type],
            $this->properties,
        );

        $standardSchema = $this->multiple ? $this->generateMultipleSchema($schema) : $schema;

        return isset($this->name) ? [$this->name => $standardSchema] : $standardSchema;
    }
}
