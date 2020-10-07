<?php

namespace Voice\OpenApi\Specification;

use Voice\OpenApi\Contracts\Serializable;
use Voice\OpenApi\Specification\Parts\Response;

class Responses implements Serializable
{
    protected array $responses = [];

    public function append(Response $response)
    {
        // + will overwrite same array keys.
        // This is okay, response keys (status codes) are unique for a single route.
        $this->responses += $response->toSchema();
    }

    public function toSchema(): array
    {
        return ['responses' => $this->responses];
    }
}
