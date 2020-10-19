<?php

declare(strict_types=1);

namespace Voice\OpenApi\Specification\Paths\Operations;

use Voice\OpenApi\Contracts\Serializable;

class Responses implements Serializable
{
    protected array $responses = [];

    public function append(Response $response): void
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
