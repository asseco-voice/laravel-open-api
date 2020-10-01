<?php

namespace Voice\OpenApi;

use Illuminate\Support\Facades\Config;
use Mpociot\Reflection\DocBlock;
use ReflectionClass;

class SchemaBuilder
{
    public array     $document;
    public Extractor $extractor;

    public function __construct()
    {
        $this->document = Config::get('asseco-open-api.general');
        $this->document['paths'] = [];
        $this->document['components'] = [];
        $this->document['components']['schemas'] = [];
    }

    public function generate(string $controllerName, string $controllerMethod, string $routePath, array $operations, array $parameters): void
    {
        $reflection = new ReflectionClass($controllerName);
        $classDocBlock = $reflection->getDocComment();

        $this->extractor = new Extractor($controllerName);
        $this->generateComponents();
        $this->addUriPath($routePath);

        foreach ($operations as $operation) {

            $methodDocBlock = new DocBlock($reflection->getMethod($controllerMethod)->getDocComment());
            $this->generateOperations($operation, $methodDocBlock, $routePath);
            $this->generateParameters($routePath, $operation, $parameters);
            $this->generateResponses($routePath, $operation);
        }
    }

    public function generateComponents(): void
    {
        $namespacedModel = $this->extractor->oneWordNamespacedModel();
        $modelColumns = $this->extractor->modelColumns();

        if (empty($modelColumns) || array_key_exists($namespacedModel, $this->document['components']['schemas'])) {
            return;
        }

        $this->document['components']['schemas'] = array_merge($this->document['components']['schemas'], [
            $namespacedModel => [
                'type'       => 'object',
                'properties' => $this->generateProperties($modelColumns),
            ]
        ]);
    }

    private function generateProperties(array $modelColumns): array
    {
        $properties = [];
        foreach ($modelColumns as $column => $type) {

            $properties = array_merge_recursive($properties, [
                $column => [
                    'type' => $type,
//                    'format' => 'map something',
                ]
            ]);
        }

        return $properties;
    }

    public function addUriPath($uri)
    {
        if (!array_key_exists($uri, $this->document['paths'])) {
            $this->document['paths'][$uri] = [];
        }
    }

    public function generateOperations(string $operation, DocBlock $methodDocBlock, string $path)
    {
//                foreach ($methodDocBlock->getTags() as $tag) {
//                    echo print_r($tag->getName(), true) . "\n";
//                }

        $operationBlock = [
            $operation => [
                'summary'     => $methodDocBlock->getShortDescription(),
                'description' => $methodDocBlock->getLongDescription()->getContents(),
                'tags'        => [
                    $this->extractor->groupTag
                ],
            ],
        ];

        $this->document['paths'][$path] = array_merge_recursive($this->document['paths'][$path], $operationBlock);
    }

    public function generateParameters(string $path, string $operation, array $parameters)
    {
        if (empty($parameters)) {
            return;
        }

        $parameterBlock = ['parameters' => []];

        $type = 'integer'; // Assume the default path parameter is integer

        // TODO: guess with multi parameters
        if ($this->extractor->model) {
            $keyName = (new $this->extractor->model)->getRouteKeyName();
            $type = $this->extractor->getTypeForColumn($keyName);
        }

        foreach ($parameters as $parameter) {

            $parameterBlock['parameters'][] = [
                'name'        => $parameter['name'],
                'in'          => 'path',
                'description' => 'desc',
                'required'    => true, // $parameter['required'] ? true : false, // OpenAPI path parameter is always required :/
                'schema'      => [
                    'type' => $type,
//                        'format' => 'map something',
                ],
            ];
        }

        $this->document['paths'][$path][$operation] = array_merge_recursive($this->document['paths'][$path][$operation], $parameterBlock);
    }

    public function generateResponses(string $path, string $operation)
    {
        $responseBlock = [
            'responses' => [
                '200' => [
                    'description' => 'Successful response',
                ],
            ],
        ];

        // TODO: keep it like this until other auto-try request is done
        if ($this->extractor->model) {
            $responseBlock['responses']['200']['content'] = $this->generateJsonResponse();
        }

        $this->document['paths'][$path][$operation] = array_merge($this->document['paths'][$path][$operation], $responseBlock);
    }

    protected function generateJsonResponse(): array
    {
        $namespacedModel = $this->extractor->oneWordNamespacedModel();

        return [
            'application/json' => [
                'schema' => [
                    '$ref' => "#/components/schemas/$namespacedModel"
                ],
            ]
        ];
    }
}
