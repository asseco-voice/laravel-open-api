<?php

declare(strict_types=1);

namespace Asseco\OpenApi\Specification\Shared\Content;

use Asseco\OpenApi\Specification\Shared\Schema;

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
