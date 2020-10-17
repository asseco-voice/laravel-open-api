<?php

namespace Voice\OpenApi\Specification\Paths\Operations;

use Voice\OpenApi\Contracts\Serializable;
use Voice\OpenApi\Specification\Shared\Content\Content;

class RequestBody implements Serializable
{
    public array $content = [];

    public function append(Content $content): void
    {
        $this->content = $content->toSchema();
    }

    public function toSchema(): array
    {
        return ['requestBody' => $this->content];
    }
}
