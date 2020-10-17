<?php

namespace Voice\OpenApi\Specification\Shared\Content;

use Voice\OpenApi\Extractor;
use Voice\OpenApi\Specification\Shared\Schema;

class JsonSchema implements ContentSchema
{
    protected array $schemas = [];

    public function generate(Extractor $extractor, bool $referenced, bool $multiple, array $modelColumns = []): void
    {
        $schema = new Schema();
        $schema->model = $extractor->responseModelName();
        $schema->referenced = $referenced;
        $schema->multiple = $multiple;
        $schema->generateProperties($modelColumns);

        $this->append($schema);
    }

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
