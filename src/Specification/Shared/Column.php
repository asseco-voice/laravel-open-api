<?php

declare(strict_types=1);

namespace Asseco\OpenApi\Specification\Shared;

class Column
{
    public string $name;
    public string $type;
    public bool $required;
    public string $description;

    public array $children = [];

    public function __construct(string $name, string $type, bool $required, string $description = '')
    {
        $this->name = $name;
        $this->type = $type;
        $this->required = $required;
        $this->description = $description;
    }

    public function append(?self $child): void
    {
        if (!$child) {
            return;
        }

        $this->children[] = $child;
    }
}
