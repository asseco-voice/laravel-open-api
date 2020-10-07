<?php

namespace Voice\OpenApi\Specification\Parts\Parameters;

use Voice\OpenApi\Specification\Parts\DataTypes\DataType;

class PathParameter implements Parameter
{
    private DataType $dataType;
    private string $name;
    private array $options;

    public function __construct(string $name, DataType $dataType, array $options = [])
    {
        $this->name = $name;
        $this->dataType = $dataType;
        $this->options = $options;
    }

    public function toSchema(): array
    {
        $schema = [
            'in'       => 'path',
            'name'     => $this->name,
            'schema'   => $this->dataType->toSchema(),
            'required' => true, // OpenApi doesn't support optional path parameters like Laravel does
            // 'description' => 'some desc',
        ];

        return array_merge($schema, $this->options);
    }
}


