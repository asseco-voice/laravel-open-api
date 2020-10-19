<?php

declare(strict_types=1);

namespace Voice\OpenApi\Specification;

use Illuminate\Support\Facades\Config;
use Voice\OpenApi\Contracts\Serializable;
use Voice\OpenApi\Specification\Components\Components;
use Voice\OpenApi\Specification\Paths\Paths;

class Document implements Serializable
{
    protected array $paths = [];
    protected array $components = [];

    public function toSchema(): array
    {
        return array_merge(
            Config::get('asseco-open-api.general'),
            $this->paths,
            $this->components,
        );
    }

    public function appendPaths(Paths $paths): void
    {
        $this->paths = $paths->toSchema();
    }

    public function appendComponents(Components $components): void
    {
        $this->components = $components->toSchema();
    }

}
