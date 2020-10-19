<?php

declare(strict_types=1);

namespace Voice\OpenApi\Specification\Paths\Operations\Parameters;

use Voice\OpenApi\Specification\Paths\Operations\Parameters\DataTypes\DataType;

class PathParameter implements Parameter
{
    protected DataType $dataType;
    protected string $name;
    protected array $options;
    protected string $description;

    public function __construct(string $name, DataType $dataType, array $options = [])
    {
        $this->name = $name;
        $this->dataType = $dataType;
        $this->options = $options;
    }

    public function addDescription(string $description): void
    {
        $this->description = $description;
    }

    public function toSchema(): array
    {
        $schema = [
            'in'          => 'path',
            'name'        => $this->name,
            'schema'      => $this->dataType->toSchema(),
            'required'    => true, // OpenApi doesn't support optional path parameters like Laravel does
            'description' => $this->description,
        ];

        return array_merge($schema, $this->options);
    }
}


