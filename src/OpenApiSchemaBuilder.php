<?php

namespace Voice\OpenApi;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Mpociot\Reflection\DocBlock;

class OpenApiSchemaBuilder
{
    public const CACHE_PREFIX = 'open_api_extractor_';

    public array     $document;
    public Extractor $extractor;

    public function __construct()
    {
        $this->document = Config::get('asseco-open-api.general');
        $this->document['paths'] = [];
        $this->document['components'] = [];
        $this->document['components']['schemas'] = [];
    }

    public function initExtractor(string $controllerName)
    {
        $cacheKey = self::CACHE_PREFIX . $controllerName;

        if (Cache::has($cacheKey) && !Config::get('asseco-open-api.bust_cache')) {
            return Cache::get($cacheKey);
        }

        $this->extractor = new Extractor($controllerName);
        Cache::put($cacheKey, $this->extractor, 60 * 60 * 24);

        return $this->extractor;
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
        $namespacedModel = $this->extractor->oneWordNamespacedModel();
        $tagName = $this->extractor->groupTag;

//                foreach ($methodDocBlock->getTags() as $tag) {
//                    echo print_r($tag->getName(), true) . "\n";
//                }

        $operationBlock = [
            $operation => [
                'summary'     => $methodDocBlock->getShortDescription(),
                'description' => $methodDocBlock->getLongDescription()->getContents(),
                'tags'        => [
                    $tagName
                ],
                'parameters'  => [$this->generateParameters($namespacedModel, 'test')],
                'responses'   => [
                    '200' => [
//                      'description' => 'Some desc',
                        'content' => $this->generateJsonResponse($namespacedModel)
                    ],
                ],
            ],
        ];

        $this->document['paths'][$path] = array_merge_recursive($this->document['paths'][$path], $operationBlock);
    }

    protected function generateParameters(string $modelName, $type): array
    {
//        $parameterName = (new $modelName)->getRouteKeyName();

        return [
//            'name'        => $parameterName,
            'in'          => 'path',
            'description' => 'desc',
            'required'    => 'true',
            'schema'      => [
                'type' => $type,
//                    'format' => 'map something',
            ],
        ];
    }

    protected function generateJsonResponse($modelName): array
    {
        return [
            'application/json' => [
                'schema' => [
                    '$ref' => "#/components/schemas/$modelName"
                ],
            ]
        ];
    }
}
