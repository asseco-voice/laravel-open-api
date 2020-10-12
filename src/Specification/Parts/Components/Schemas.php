<?php

namespace Voice\OpenApi\Specification\Parts\Components;

use Voice\OpenApi\Specification\Parts\Components\Models\Schema;

class Schemas implements Components
{
    protected array $schemas = [];

    public function generate($name, $modelColumns): void
    {
        $schema = new Schema();
        $schema->name = $name;
        $schema->generateProperties($modelColumns);

        $this->append($schema);
    }

    public function append(Schema $schema): void
    {
        if (array_key_exists($schema->name, $this->schemas)) {
            return;
        }

        // + will overwrite same array keys.
        // This is okay, schemas are unique.
        $this->schemas = $schema->toSchema();
    }

    public function toSchema(): array
    {
        return $this->schemas;
    }
}
