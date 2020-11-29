<?php

declare(strict_types=1);

namespace Asseco\OpenApi\Specification\Paths\Operations;

use Asseco\OpenApi\Contracts\Serializable;
use Asseco\OpenApi\Specification\Shared\Content\Content;

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
