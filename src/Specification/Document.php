<?php

declare(strict_types=1);

namespace Asseco\OpenApi\Specification;

use Asseco\OpenApi\Contracts\Serializable;
use Asseco\OpenApi\Specification\Components\Components;
use Asseco\OpenApi\Specification\Paths\Paths;

class Document implements Serializable
{
    protected array $paths = [];
    protected array $components = [];

    public function toSchema(): array
    {
        return array_merge(
            config('asseco-open-api.general'),
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
