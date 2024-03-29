<?php

namespace Asseco\OpenApi;

use Asseco\OpenApi\Specification\Paths\Operations\RequestBody;
use Asseco\OpenApi\Specification\Shared\Column;
use Asseco\OpenApi\Specification\Shared\Content\Content;
use Asseco\OpenApi\Specification\Shared\Content\JsonSchema;
use Asseco\OpenApi\Specification\Shared\ReferencedSchema;
use Asseco\OpenApi\Specification\Shared\StandardSchema;
use Illuminate\Database\Eloquent\Model;

class RequestGenerator
{
    private TagExtractor $tagExtractor;
    private string $schemaName;

    public function __construct(TagExtractor $tagExtractor, string $schemaName)
    {
        $this->tagExtractor = $tagExtractor;
        $this->schemaName = $schemaName;
    }

    public function createSchema(string $namespace, ?Model $model): ?StandardSchema
    {
        $requestColumns = $this->getRequestColumns($namespace, $model);

        $schema = new StandardSchema($this->schemaName);
        $schema->generateProperties($requestColumns);

        return $schema;
    }

    public function getBody(): RequestBody
    {
        $schema = new ReferencedSchema($this->schemaName);

        $jsonRequestSchema = new JsonSchema();
        $jsonRequestSchema->append($schema);

        $requestContent = new Content();
        $requestContent->append($jsonRequestSchema);

        $requestBody = new RequestBody();
        $requestBody->append($requestContent);

        return $requestBody;
    }

    protected function getRequestColumns(string $namespace, ?Model $model): array
    {
        $methodRequestColumns = $this->tagExtractor->getRequest();

        if ($methodRequestColumns) {
            return $methodRequestColumns;
        }

        $appendedColumns = $this->getColumnsToAppend($namespace);

        if ($model) {
            $modelColumns = ModelColumns::get($model);

            $except = $this->tagExtractor->getExceptAttributes();

            return $this->extractRequestData($model, $modelColumns, $except, $appendedColumns);
        }

        return [];
    }

    protected function getColumnsToAppend(string $namespace): array
    {
        $toAppend = $this->tagExtractor->getRequestAppendAttributes($namespace);

        $appendedColumns = [];

        foreach ($toAppend as $item) {
            $appendedColumn = new Column($item['key'], 'object', true);

            $appendedModelColumns = ModelColumns::get($item['model']);
            $appendedModelRequestData = $this->extractRequestData($item['model'], $appendedModelColumns, []);

            foreach ($appendedModelRequestData as $child) {
                $appendedColumn->append($child);
            }

            $appendedColumns[] = $appendedColumn;
        }

        return $appendedColumns;
    }

    private function extractRequestData(Model $model, array $columns, array $except, array $append = []): array
    {
        $fillable = $model->getFillable();
        $guarded = $model->getGuarded();

        if (!empty($fillable)) {
            foreach ($columns as $key => $column) {
                if (!in_array($column->name, $fillable) || in_array($column->name, $except)) {
                    unset($columns[$key]);
                }
            }
        } elseif (!empty($guarded)) {
            foreach ($columns as $key => $column) {
                if (in_array($column->name, $guarded) || in_array($column->name, $except)) {
                    unset($columns[$key]);
                }
            }
        } else {
            $columns = [];
        }

        foreach ($append as $item) {
            $columns[] = $item;
        }

        return $columns;
    }
}
