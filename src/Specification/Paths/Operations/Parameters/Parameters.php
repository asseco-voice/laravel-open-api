<?php

namespace Voice\OpenApi\Specification\Paths\Operations\Parameters;

use Voice\OpenApi\Contracts\Serializable;

class Parameters implements Serializable
{
    public array $parameters = [];

    public function append(Parameter $parameter)
    {
        $this->parameters[] = $parameter->toSchema();
    }

    public function toSchema(): array
    {
        return ['parameters' => $this->parameters];
    }
}
