<?php

namespace Voice\OpenApi\Specification\Parts\Components\Models;

use Voice\OpenApi\Specification\Parts\Models\Properties;

class Schema implements Component
{
    public string $name;
    protected array $properties = [];

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function generateProperties($modelColumns)
    {
        $properties = new Properties($modelColumns);

        $this->appendProperties($properties);
    }

    protected function appendProperties(Properties $properties)
    {
        $this->properties = $properties->toSchema();
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
