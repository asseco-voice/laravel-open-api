<?php

namespace Voice\OpenApi\Specification\Components\Parts;

use Voice\OpenApi\Specification\Shared\Schema;

class Schemas implements Components
{
    protected array $schemas = [];

    public function toSchema(): array
    {
        return $this->schemas;
    }

    public function append(Schema $schema): void
    {
        if (array_key_exists($schema->name, $this->schemas)) {
            return;
        }

        // + will overwrite same array keys.
        // This is okay, schemas are unique.
        $this->schemas += $schema->toSchema();
    }

}
