<?php

declare(strict_types=1);

namespace Voice\OpenApi\Specification\Paths;

use Voice\OpenApi\Contracts\Serializable;

class Paths implements Serializable
{
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
