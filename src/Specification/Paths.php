<?php

namespace Voice\OpenApi\Specification;

use Voice\OpenApi\Contracts\Serializable;
use Voice\OpenApi\Extractor;
use Voice\OpenApi\RouteWrapper;
use Voice\OpenApi\Specification\Parts\Path;
use Voice\OpenApi\Traits\MergesArrays;

class Paths implements Serializable
{
    use MergesArrays;

    protected array $paths = [];

    public function generatePath(RouteWrapper $route, Extractor $extractor)
    {
        $path = new Path($route, $extractor);

        $path->generateOperation();

        $this->append($path);
    }

    public function append(Path $path)
    {
        $this->paths = array_merge_recursive($this->paths, $path->toSchema());
    }

    public function toSchema(): array
    {
        return ['paths' => $this->paths];
    }
}
