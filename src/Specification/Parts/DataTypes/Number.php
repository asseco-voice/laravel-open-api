<?php

namespace Voice\OpenApi\Specification\Parts\DataTypes;

class Number implements DataType
{
    protected const FORMATS = ['float', 'double'];

    public function __construct()
    {
    }

    public function toSchema(): array
    {
        // TODO: Implement toSchema() method.
    }
}
