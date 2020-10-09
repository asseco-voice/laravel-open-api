<?php

namespace Voice\OpenApi\Specification\Parts\Components\Models;

class Response
{
    protected string $name;
    protected array $properties = [];

    public function __construct(string $name)
    {
        $this->name = $name;
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
