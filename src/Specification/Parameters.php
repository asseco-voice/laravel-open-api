<?php

namespace Voice\OpenApi\Specification;

use Voice\OpenApi\Contracts\Serializable;
use Voice\OpenApi\Specification\Parts\Parameters\Parameter;

class Parameters implements Serializable
{
    protected array $parameters = [];

    public function append(Parameter $parameter)
    {
        $this->parameters[] = $parameter->toSchema();
    }

    public function toSchema(): array
    {
        return ['parameters' => $this->parameters];
    }
}
