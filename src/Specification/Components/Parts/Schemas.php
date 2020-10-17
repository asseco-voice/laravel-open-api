<?php

namespace Voice\OpenApi\Specification\Components\Parts;

use Voice\OpenApi\Specification\Shared\StandardSchema;

class Schemas implements Components
{
    protected array $schemas = [];

    public function toSchema(): array
    {
        return $this->schemas;
    }

    public function append(StandardSchema $schema, bool $referenced = false): void
    {
        if (array_key_exists($schema->name, $this->schemas)) {
            return;
        }

        $schema->referenced = $referenced;

        // + will overwrite same array keys.
        // This is okay, schemas are unique.
        $this->schemas += $schema->toSchema();
    }

}
