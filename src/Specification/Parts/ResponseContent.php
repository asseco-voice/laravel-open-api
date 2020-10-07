<?php

namespace Voice\OpenApi\Specification\Parts;

use Voice\OpenApi\Contracts\Serializable;

class ResponseContent implements Serializable
{
    private string $model;

    public function __construct(string $model)
    {
        $this->model = $model;
    }

    public function toSchema(): array
    {
        // TODO: if model exists then this, otherwise try to make a request

        return $this->model ? [
            'content' => [
                'application/json' => [
                    'schema' => [
                        '$ref' => "#/components/schemas/$this->model",
                    ],
                ],
            ],
        ] : [];
    }
}
