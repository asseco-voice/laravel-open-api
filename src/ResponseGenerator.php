<?php

namespace Asseco\OpenApi;

use Asseco\OpenApi\Specification\Paths\Operations\Response;
use Asseco\OpenApi\Specification\Paths\Operations\Responses;
use Asseco\OpenApi\Specification\Shared\Content\Content;
use Asseco\OpenApi\Specification\Shared\Content\JsonSchema;
use Asseco\OpenApi\Specification\Shared\ReferencedSchema;
use Asseco\OpenApi\Specification\Shared\StandardSchema;
use Illuminate\Database\Eloquent\Model;

class ResponseGenerator
{
    protected TagExtractor $tagExtractor;
    protected StandardSchema $schema;
    private string $schemaName;

    public function __construct(TagExtractor $tagExtractor, string $schemaName)
    {
        $this->tagExtractor = $tagExtractor;
        $this->schemaName = $schemaName;
    }

    public function generate(string $routeOperation, bool $routeHasPathParameters, bool $hasSchema): Responses
    {
        $response = new Response('200', 'Successful response.');

        if ($hasSchema) {
            $schema = new ReferencedSchema($this->schemaName);
            $schema->multiple = $this->isMultiple($routeOperation, $routeHasPathParameters);

            $jsonResponseSchema = new JsonSchema();
            $jsonResponseSchema->append($schema);

            $responseContent = new Content();
            $responseContent->append($jsonResponseSchema);

            $response->append($responseContent);
        }

        $responses = new Responses();
        $responses->append($response);

        return $responses;
    }

    public function createSchema(?Model $model): ?StandardSchema
    {
        $responseColumns = $this->getResponseColumns($model);

        if (empty($responseColumns)) {
            return null;
        }

        $schema = new StandardSchema($this->schemaName);
        $schema->generateProperties($responseColumns);

        return $schema;
    }

    protected function getResponseColumns(?Model $model): array
    {
        $methodResponseColumns = $this->tagExtractor->getResponse();

        if ($methodResponseColumns) {
            return $methodResponseColumns;
        }

        if ($model) {
            $modelColumns = new ModelColumns($model);

            return $this->extractResponseData($model, $modelColumns->modelColumns());
        }

        return [];
    }

    protected function extractResponseData(Model $model, array $columns): array
    {
        $hidden = $model->getHidden();

        foreach ($columns as $column => $type) {
            if (in_array($column, $hidden)) {
                unset($columns[$column]);
            }
        }

        return $columns;
    }

    public function isMultiple(string $routeOperation, bool $routeHasPathParameters): bool
    {
        $hasMultipleTag = $this->tagExtractor->hasMultipleTag();

        if ($hasMultipleTag) {
            return $this->tagExtractor->isResponseMultiple();
        }

        return $routeOperation === 'get' && !$routeHasPathParameters;
    }
}
