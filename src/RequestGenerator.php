<?php

namespace Voice\OpenApi;

use Illuminate\Database\Eloquent\Model;
use Voice\OpenApi\Specification\Paths\Operations\RequestBody;
use Voice\OpenApi\Specification\Shared\Content\Content;
use Voice\OpenApi\Specification\Shared\Content\JsonSchema;
use Voice\OpenApi\Specification\Shared\ReferencedSchema;
use Voice\OpenApi\Specification\Shared\StandardSchema;

class RequestGenerator
{
    private ReflectionExtractor $reflectionExtractor;

    public function __construct(ReflectionExtractor $reflectionExtractor)
    {
        $this->reflectionExtractor = $reflectionExtractor;
    }

    public function createSchema(string $requestModelName, ?Model $model): ?StandardSchema
    {
        $requestColumns = $this->getRequestColumns($model);

        $schema = new StandardSchema($requestModelName);
        $schema->generateProperties($requestColumns);

        return $schema;
    }

    public function getBody(string $requestModelName): RequestBody
    {
        $schema = new ReferencedSchema($requestModelName);

        $jsonRequestSchema = new JsonSchema();
        $jsonRequestSchema->append($schema);

        $requestContent = new Content();
        $requestContent->append($jsonRequestSchema);

        $requestBody = new RequestBody();
        $requestBody->append($requestContent);

        return $requestBody;
    }

    protected function getRequestColumns(?Model $model): array
    {
        $methodRequestColumns = $this->reflectionExtractor->getRequest();

        if ($methodRequestColumns) {
            return $methodRequestColumns;
        }

        if ($model) {
            $modelColumns = new ModelColumns($model);
            $except = $this->reflectionExtractor->getExceptAttributes();

            return $this->extractRequestData($model, $modelColumns->modelColumns(), $except);
        }


        return [];
    }

    private function extractRequestData(Model $model, array $columns, array $except): array
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

        return $columns;
    }

}
