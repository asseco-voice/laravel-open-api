<?php

namespace Voice\OpenApi\Specification\Parts;

use Voice\OpenApi\Contracts\Serializable;

class Responses implements Serializable
{
    protected array $responses = [];

    public function generateResponse(?string $model, string $responseModelName, bool $multiple): void
    {
        $response = new Response('200', 'Successful request.', []);

        if ($model) {
            $response->generateContent($responseModelName, $multiple);
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
