<?php

namespace Voice\OpenApi\Specification\Shared\Content;

use Voice\OpenApi\Specification\Shared\Schema;

class JsonSchema implements ContentSchema
{
    protected array $schemas = [];

    public function append(Schema $schema): void
    {
        // + will overwrite same array keys.
        // This is okay, schemas are unique.
        $this->schemas = $schema->toSchema();
    }

    public function toSchema(): array
    {
        return ['schema' => $this->schemas];
    }
}
