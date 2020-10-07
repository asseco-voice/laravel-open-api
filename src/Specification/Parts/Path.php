<?php

namespace Voice\OpenApi\Specification\Parts;

use Voice\OpenApi\Contracts\Serializable;
use Voice\OpenApi\Traits\MergesArrays;

class Path implements Serializable
{
    use MergesArrays;

    protected string $path;
    protected array $operations = [];

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function append(Operation $operation)
    {
        // + will overwrite same array keys.
        // This is okay, operations are unique for a single route.
        $this->operations += $operation->toSchema();
    }

    public function toSchema(): array
    {
        return [$this->path => $this->operations];
    }
}
