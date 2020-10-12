<?php

namespace Voice\OpenApi\Specification\Parts;

use Voice\OpenApi\Contracts\Serializable;

class Response implements Serializable
{
    private string $statusCode;
    private string $description;
    private array $options;
    private array $content = [];

    public function __construct(string $statusCode, string $description, array $options = [])
    {
        $this->statusCode = $statusCode;
        $this->description = $description;
        $this->options = $options;
    }

    public function generateContent($model, bool $multiple)
    {
        $content = new ResponseContent($model);
        $content->generateResponses($multiple);

        $this->appendContent($content);
    }

    public function appendContent(ResponseContent $responseContent)
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
