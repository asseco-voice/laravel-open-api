<?php

namespace Voice\OpenApi;

use Illuminate\Database\Eloquent\Model;
use Voice\OpenApi\Specification\Paths\Operations\Response;
use Voice\OpenApi\Specification\Paths\Operations\Responses;
use Voice\OpenApi\Specification\Shared\Content\Content;
use Voice\OpenApi\Specification\Shared\Content\JsonSchema;
use Voice\OpenApi\Specification\Shared\ReferencedSchema;
use Voice\OpenApi\Specification\Shared\StandardSchema;

class ResponseGenerator
{
    protected TagExtractor $reflectionExtractor;
    protected StandardSchema $schema;
    private string $schemaName;

    public function __construct(TagExtractor $reflectionExtractor, string $schemaName)
    {
        $this->reflectionExtractor = $reflectionExtractor;
        $this->schemaName = $schemaName;
    }

    public function generate(string $routeOperation, bool $routeHasPathParameters): Responses
    {
        $schema = new ReferencedSchema($this->schemaName);
        $schema->multiple = $this->isMultiple($routeOperation, $routeHasPathParameters);

        $jsonResponseSchema = new JsonSchema();
        $jsonResponseSchema->append($schema);

        $responseContent = new Content();
        $responseContent->append($jsonResponseSchema);

        // Default, osim ako nije overridan preko metode
        // ovo mora biti foreachano
        $response = new Response('200', 'Successful response.');
        $response->append($responseContent);

        $responses = new Responses();
        $responses->append($response);

        return $responses;
    }

    public function createSchema(?Model $model): StandardSchema
    {
        $responseColumns = $this->getResponseColumns($model);

        $schema = new StandardSchema($this->schemaName);
        $schema->generateProperties($responseColumns);

        return $schema;
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
        $methodData = $this->reflectionExtractor->isResponseMultiple();

        if ($methodData) {
            return true;
        }

        return $routeOperation === 'get' && !$routeHasPathParameters;
    }

}
