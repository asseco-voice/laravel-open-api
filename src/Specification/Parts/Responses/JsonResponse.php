<?php

namespace Voice\OpenApi\Specification\Parts\Responses;

use Voice\OpenApi\Specification\Parts\Components\Models\Schema;

class JsonResponse implements Response
{
    protected array $schemas = [];

    public function generate(string $model, bool $referenced, bool $multiple, array $modelColumns = []): void
    {
        // TODO: if model exists then this, otherwise try to make a request

        $schema = new Schema();
        $schema->model = $model;
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
