<?php

namespace Voice\OpenApi\Specification\Paths\Operations\Parameters\DataTypes;

class ArrayType extends DataType
{
    public function toSchema(): array
    {
        $schema = [
            'type' => 'array',
        ];

        return array_merge_recursive($schema, $this->options);
    }
}
