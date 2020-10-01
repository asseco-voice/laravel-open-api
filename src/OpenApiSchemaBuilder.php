<?php

namespace Voice\OpenApi;

use Illuminate\Support\Facades\Config;
use Mpociot\Reflection\DocBlock;

class OpenApiSchemaBuilder
{
    public array $document;

    public function __construct()
    {
        $this->document = Config::get('asseco-open-api.general');
        $this->document['paths'] = [];
        $this->document['components'] = [];
        $this->document['components']['schemas'] = [];
    }

    public function generateComponents(string $namespacedModel, array $modelColumns): void
    {
        if (empty($modelColumns) || array_key_exists($namespacedModel, $this->document['components']['schemas'])) {
            return;
        }

        $this->document['components']['schemas'] = array_merge($this->document['components']['schemas'], [
            $namespacedModel => [
                'type'       => 'object',
                'properties' => $this->getProperties($modelColumns),
            ]
        ]);
    }

    private function getProperties(array $modelColumns): array
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

    public function generateDoc(string $method, DocBlock $methodDocBlock, string $tagName, string $modelName, string $path)
    {
//                foreach ($methodDocBlock->getTags() as $tag) {
//                    echo print_r($tag->getName(), true) . "\n";
//                }

        $methodBlock = $this->generateMethodBlock($method, $methodDocBlock, $tagName, $modelName);

        $this->document['paths'][$path] = array_merge_recursive($this->document['paths'][$path], $methodBlock);
    }

    protected function generateMethodBlock(string $method, DocBlock $methodDocBlock, string $tagName, string $modelName): array
    {
        $methodBlock = [
            $method => [
                'summary'     => $methodDocBlock->getShortDescription(),
                'description' => $methodDocBlock->getLongDescription()->getContents(),
                'tags'        => [
                    $tagName
                ],
                'responses'   => [
                    '200' => [
                        'description' => 'Some desc',
                        'content'     => [
                            'application/json' => [
//                                'schema' => [
//                                    'type' => 'array',
//                                    'items' => [
//                                        'type' => 'string',
//                                    ],
//                                ],

                                'schema' => [
                                    '$ref' => "#/components/schemas/$modelName"
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        ];

        return $methodBlock;
    }
}
