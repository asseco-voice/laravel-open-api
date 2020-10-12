<?php

namespace Voice\OpenApi\Specification\Parts\Components;

use Voice\OpenApi\Extractor;
use Voice\OpenApi\Specification\Parts\Components\Models\Schema;

class Schemas implements Components
{
    protected array $schemas = [];

    public function generate(Extractor $extractor): void
    {
        $modelColumns = $extractor->modelColumns();

        $this->generateRequestSchema($extractor->requestModelName(), $modelColumns);
        $this->generateResponseSchema($extractor->responseModelName(), $modelColumns);
    }

    public function toSchema(): array
    {
        return $this->schemas;
    }

    protected function generateRequestSchema($name, $modelColumns): void
    {
        $columns = $this->cleanup($modelColumns);

        $this->generateSchema($name, $columns);
    }

    protected function cleanup($modelColumns)
    {
        return array_filter($modelColumns, function ($column) {
            return !in_array($column, ['id', 'created_at', 'updated_at']);
        }, ARRAY_FILTER_USE_KEY);
    }

    protected function generateResponseSchema($name, $modelColumns): void
    {
        $this->generateSchema($name, $modelColumns);
    }

    protected function generateSchema($name, $modelColumns): void
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
        $this->schemas += $schema->toSchema();
    }

}
