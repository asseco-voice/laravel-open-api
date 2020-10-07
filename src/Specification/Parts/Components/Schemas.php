<?php

namespace Voice\OpenApi\Specification\Parts\Components;

use Voice\OpenApi\Specification\Parts\Models\Properties;

class Schemas implements Components
{
    protected string $name;
    protected array $properties = [];

    public function __construct(string $name, ?Properties $properties = null)
    {
        $this->name = $name;

        if ($properties) {
            $this->properties = $properties->toSchema();
        }
    }

    public function toSchema(): array
    {
        $schema = array_merge(
            ['type' => 'object'],
            $this->properties,
        );

        return [$this->name => $schema];
    }
}
