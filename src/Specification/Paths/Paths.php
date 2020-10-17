<?php

namespace Voice\OpenApi\Specification\Paths;

use Voice\OpenApi\Contracts\Serializable;
use Voice\OpenApi\Traits\MergesArrays;

class Paths implements Serializable
{
    use MergesArrays;

    protected array $paths = [];

    public function append(Path $path)
    {
        $this->paths = array_merge_recursive($this->paths, $path->toSchema());
    }

    public function toSchema(): array
    {
        return ['paths' => $this->paths];
    }
}
