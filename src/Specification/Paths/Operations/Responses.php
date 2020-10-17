<?php

namespace Voice\OpenApi\Specification\Paths\Operations;

use Voice\OpenApi\Contracts\Serializable;

class Responses implements Serializable
{
    protected array $responses = [];

    public function generateResponse(bool $multiple, string $statusCode, string $description): void
    {
        $response = new Response($statusCode, $description);

        if ($this->extractor->model) {
            $response->generateContent($multiple);
        }

        $this->append($response);
    }

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
