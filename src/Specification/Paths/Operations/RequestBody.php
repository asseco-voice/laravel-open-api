<?php

namespace Voice\OpenApi\Specification\Paths\Operations;

use Voice\OpenApi\Contracts\Serializable;
use Voice\OpenApi\Specification\Shared\Content\Content;

class RequestBody implements Serializable
{
    public string $description;
    public bool $required;
    public array $content = [];

    public function generateContent(): void
    {
        $content = new Content();

        $content->generate(false);

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
