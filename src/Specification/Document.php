<?php

namespace Voice\OpenApi\Specification;

use Illuminate\Support\Facades\Config;
use Voice\OpenApi\Contracts\Serializable;
use Voice\OpenApi\Specification\Parts\Components\Components;

class Document implements Serializable
{
    protected array $paths = [];
    protected array $components = [];

    public function toSchema(): array
    {
        return array_merge(
            Config::get('asseco-open-api.general'),
            $this->paths,
            ['components' =>
                 ['schemas' => $this->components]],
        );
    }

    public function appendPaths(Paths $paths)
    {
        $this->paths = $paths->toSchema();
    }

    public function appendComponents(Components $component)
    {
        $this->components = array_merge($this->components, $component->toSchema());
    }

}
