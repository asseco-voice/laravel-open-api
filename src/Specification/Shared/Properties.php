<?php

namespace Voice\OpenApi\Specification\Shared;

use Voice\OpenApi\Contracts\Serializable;

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

            $columnValues = [
                $column->name => [
                    'type'        => $column->type,
                    'description' => $column->description,
                    //'format' => 'map something',
                ],
            ];

            if ($column->children) {
                foreach ($column->children as $child) {

                    $columnValues[$column->name] = array_merge_recursive($columnValues[$column->name], [
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
