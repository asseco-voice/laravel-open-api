<?php

declare(strict_types=1);

namespace Asseco\OpenApi\Specification\Shared;

use Asseco\OpenApi\Contracts\Serializable;

class Properties implements Serializable
{
    private array $modelColumns;

    public function __construct(array $modelColumns)
    {
        $this->modelColumns = $modelColumns;
    }

    public function toSchema(): array
    {
        [$properties, $required] = $this->parseColumns();

        if(array_key_exists('example', $properties)) {
            return $properties;
        }

        return [
            'properties' => $properties,
            'required'   => $required,
        ];
    }

    private function parseColumns(): array
    {
        $properties = [];
        $required = [];

        foreach ($this->modelColumns as $column) {
            if (gettype($column) == 'string'){
                $columnValues = [
                    'type' => 'string',
                    'example' => $column
                ];
                $properties = array_merge_recursive($properties, $columnValues);
                continue;
            }

            $columnValues = [
                $column->name => [
                    'type'        => $column->type,
                    'description' => $column->description,
                    //'format' => 'map something',
                ],
            ];

            if ($column->children) {
                foreach ($column->children as $child) {
                    if ($column->type === 'object') {
                        $columnValues[$column->name] = array_merge_recursive($columnValues[$column->name],
                            [
                                'properties' => [
                                    $child->name => [
                                        'type' => $child->type,
                                    ],
                                ],
                            ]);

                        continue;
                    }

                    $columnValues[$column->name] = array_merge_recursive($columnValues[$column->name],
                        [
                            'items' => [
                                'type' => $child->type,
                            ],
                        ]);
                }
            }

            $properties = array_merge_recursive($properties, $columnValues);

            if ($column->required) {
                $required[] = $column->name;
            }
        }

        return [$properties, $required];
    }
}
