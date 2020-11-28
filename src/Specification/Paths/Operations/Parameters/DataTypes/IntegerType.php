<?php

declare(strict_types=1);

namespace Asseco\OpenApi\Specification\Paths\Operations\Parameters\DataTypes;

class IntegerType extends DataType
{
    public function toSchema(): array
    {
        $schema = [
            'type' => 'integer',
        ];

        return array_merge_recursive($schema, $this->options);
    }
}
