<?php


namespace Voice\OpenApi\Specification;


use Voice\OpenApi\Contracts\Serializable;
use Voice\OpenApi\Specification\Parts\Path;
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
