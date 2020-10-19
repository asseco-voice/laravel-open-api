<?php

declare(strict_types=1);

namespace Voice\OpenApi\Specification\Paths\Operations\Parameters;

use Voice\OpenApi\Contracts\Serializable;

class Parameters implements Serializable
{
    public array $parameters = [];

    public function append(Parameter $parameter): void
    {
        $this->parameters[] = $parameter->toSchema();
    }

    public function toSchema(): array
    {
        return ['parameters' => $this->parameters];
    }
}
