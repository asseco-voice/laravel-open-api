<?php

declare(strict_types=1);

namespace Asseco\OpenApi\Specification\Paths\Operations\Parameters;

use Asseco\OpenApi\Contracts\Serializable;

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
