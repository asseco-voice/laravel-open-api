<?php

declare(strict_types=1);

namespace Voice\OpenApi\Specification\Paths\Operations\Parameters\DataTypes;

class StringType extends DataType
{
    public function toSchema(): array
    {
        $schema = [
            'type' => 'string',
        ];

        return array_merge_recursive($schema, $this->options);
    }
}
