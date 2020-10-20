<?php

declare(strict_types=1);

namespace Voice\OpenApi\Specification\Components\Parts;

use Voice\OpenApi\Specification\Shared\Schema;

class Schemas implements Component
{
    protected array $schemas = [];

    public function toSchema(): array
    {
        return $this->schemas;
    }

    public function append(?Schema $schema): void
    {
        if (!$schema) {
            return;
        }

        // + will overwrite same array keys.
        // This is okay, schemas are unique.
        $this->schemas += $schema->toSchema();
    }
}
