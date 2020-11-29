<?php

declare(strict_types=1);

namespace Asseco\OpenApi\Specification\Paths\Operations\Parameters\DataTypes;

class NumberType extends DataType
{
    public function toSchema(): array
    {
        $schema = [
            'type' => 'number',
        ];

        return array_merge_recursive($schema, $this->options);
    }
}
