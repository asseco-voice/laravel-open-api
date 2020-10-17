<?php

namespace Voice\OpenApi\Specification\Shared;

use Voice\OpenApi\Contracts\Serializable;

class Properties implements Serializable
{
    private array $modelColumns;

    protected array $properties = [];
    protected array $required = [];

    public function __construct(array $modelColumns)
    {
        $this->modelColumns = $modelColumns;
    }

    public function toSchema(): array
    {
        $this->parseColumns();

        return [
            'properties' => $this->properties,
            'required'   => array_unique($this->required),
        ];
    }

    private function parseColumns(): void
    {
        foreach ($this->modelColumns as $column) {

            $this->properties = array_merge_recursive($this->properties, [
                $column->name => [
                    'type' => $column->type,
                    'description' => $column->description,
                    //'format' => 'map something',
                ]
            ]);

            if($column->required){
                $this->required[] = $column->name;
            }
        }
    }

}
