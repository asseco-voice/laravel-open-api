<?php

declare(strict_types=1);

namespace Voice\OpenApi\Specification\Paths\Operations\Parameters\DataTypes;

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
