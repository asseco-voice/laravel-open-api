<?php

namespace Voice\OpenApi\Specification\Parts\Models;

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
        return [
            'properties' => $this->parseColumns(),
        ];
    }

    private function parseColumns(): array
    {
        $properties = [];
        foreach ($this->modelColumns as $column => $type) {

            $properties = array_merge_recursive($properties, [
                $column => [
                    'type' => $type,
                    //                    'format' => 'map something',
                ]
            ]);
        }

        return $properties;
    }

}
