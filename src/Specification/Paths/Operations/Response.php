<?php

declare(strict_types=1);

namespace Voice\OpenApi\Specification\Paths\Operations;

use Voice\OpenApi\Contracts\Serializable;
use Voice\OpenApi\Specification\Shared\Content\Content;

class Response implements Serializable
{
    protected string $statusCode;
    protected string $description;
    protected array $options;
    protected array $content = [];

    public function __construct(string $statusCode, string $description, array $options = [])
    {
        $this->statusCode = $statusCode;
        $this->description = $description;
        $this->options = $options;
    }

    public function append(Content $responseContent): void
    {
        $this->content = array_merge_recursive($this->content, $responseContent->toSchema());
    }

    public function toSchema(): array
    {
        $schema = array_merge_recursive(
            ['description' => $this->description],
            $this->options,
            $this->content
        );

        return [$this->statusCode => $schema];
    }
}
