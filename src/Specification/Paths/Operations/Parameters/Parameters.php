<?php

namespace Voice\OpenApi\Specification\Paths\Operations\Parameters;

use Voice\OpenApi\Contracts\Serializable;
use Voice\OpenApi\Specification\Paths\Operations\Parameters\Parameter;

class Parameters implements Serializable
{
    public array $parameters = [];

    public function __invoke(array $pathParameters): self
    {
        foreach ($pathParameters as $parameter) {
            $this->append($parameter);
        }

        return $this;
    }

    public function append(Parameter $parameter)
    {
        $this->parameters[] = $parameter->toSchema();
    }

    public function toSchema(): array
    {
        return ['parameters' => $this->parameters];
    }
}
