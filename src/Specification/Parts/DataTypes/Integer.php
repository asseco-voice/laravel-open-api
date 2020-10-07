<?php

namespace Voice\OpenApi\Specification\Parts\DataTypes;

class Integer implements DataType
{
    private array $options;

    public function __construct(array $options = [])
    {
        $this->options = $options;
    }

    public function toSchema(): array
    {
        $schema = [
            'type' => 'integer',
        ];

        return array_merge_recursive($schema, $this->options);
    }
}
