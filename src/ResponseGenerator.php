<?php

namespace Voice\OpenApi;

use Illuminate\Database\Eloquent\Model;
use Voice\OpenApi\Specification\Paths\Operations\Response;
use Voice\OpenApi\Specification\Paths\Operations\Responses;
use Voice\OpenApi\Specification\Shared\Content\Content;
use Voice\OpenApi\Specification\Shared\Content\JsonSchema;
use Voice\OpenApi\Specification\Shared\Schema;

class ResponseGenerator
{
    protected ReflectionExtractor $reflectionExtractor;
    protected Schema $schema;

    public function __construct(ReflectionExtractor $reflectionExtractor, string $modelName)
    {
        $this->reflectionExtractor = $reflectionExtractor;
        $this->schema = new Schema($modelName);
    }

    public function generate(string $routeOperation, bool $routeHasPathParameters): Responses
    {
        $this->schema->referenced = true;
        $this->schema->multiple = $this->isMultiple($routeOperation, $routeHasPathParameters);

        $jsonResponseSchema = new JsonSchema();
        $jsonResponseSchema->append($this->schema);

        $responseContent = new Content();
        $responseContent->append($jsonResponseSchema);

        // Default, osim ako nije overridan preko metode
        // ovo mora biti foreachano
        $response = new Response('200', 'Successful response.');
        $response->appendContent($responseContent);

        $responses = new Responses();
        $responses->append($response);

        return $responses;
    }

    public function getSchema(?Model $model): Schema
    {
        $responseColumns = $this->getResponseColumns($model);

        $this->schema->generateProperties($responseColumns);

        return $this->schema;
    }

    protected function getResponseColumns(?Model $model): array
    {
        $methodResponseColumns = $this->reflectionExtractor->getResponse();

        if ($methodResponseColumns) {
            return $methodResponseColumns;
        }

        if ($model) {
            $modelColumns = new ModelColumns($model);

            return $this->extractResponseData($model, $modelColumns->modelColumns());
        }

        return [];
    }

    private function extractResponseData(Model $model, array $columns): array
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
        $methodData = $this->reflectionExtractor->getMultiple();

        if ($methodData) {
            return true;
        }

        return $routeOperation === 'get' && !$routeHasPathParameters;
    }

}
