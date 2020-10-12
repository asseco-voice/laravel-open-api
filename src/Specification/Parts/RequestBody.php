<?php

namespace Voice\OpenApi\Specification\Parts;

use Voice\OpenApi\Contracts\Serializable;

class RequestBody implements Serializable
{
    public string $description;
    public bool $required;
    public array $content = [];

    public function generateContent($model): void
    {
        $content = new Content($model);
        $content->generateResponses(false);

        $this->append($content);
    }

    public function append(Content $content): void
    {
        $this->content = $content->toSchema();
    }

    public function toSchema(): array
    {
        return ['requestBody' => $this->content];
    }
}
