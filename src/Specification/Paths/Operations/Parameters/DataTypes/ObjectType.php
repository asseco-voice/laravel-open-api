<?php

declare(strict_types=1);

namespace Voice\OpenApi\Specification\Paths\Operations\Parameters\DataTypes;

class ObjectType extends DataType
{
    public function toSchema(): array
    {
        $schema = [
            'type' => 'object',
        ];

        return array_merge_recursive($schema, $this->options);
    }
}
