<?php

namespace Voice\OpenApi\Specification\Paths\Operations\Parameters\DataTypes;

class BooleanType extends DataType
{
    public function toSchema(): array
    {
        $schema = [
            'type' => 'boolean',
        ];

        return array_merge_recursive($schema, $this->options);
    }
}
