<?php

namespace Voice\OpenApi\Specification\Parts;

use Voice\OpenApi\Contracts\Serializable;
use Voice\OpenApi\Exceptions\OpenApiException;
use Voice\OpenApi\Specification\Parts\Responses\JsonResponse;
use Voice\OpenApi\Specification\Parts\Responses\Response as ResponseInterface;

class Content implements Serializable
{
    private string $model;

    protected array $content = [];

    protected array $registered = [
        'application/json' => JsonResponse::class
    ];

    public function __construct(string $model)
    {
        $this->model = $model;
    }

    public function generateResponses(bool $multiple)
    {
        foreach ($this->registered as $contentKey => $contentClass) {
            $content = $this->initializeContent($contentKey, $contentClass);

            $content->generate($this->model, true, $multiple);
            $this->append($contentKey, $content);
        }
    }

    protected function initializeContent(string $contentKey, string $contentClass): ResponseInterface
    {
        $content = new $contentClass();

        if (!$content instanceof ResponseInterface) {
            throw new OpenApiException("Response '$contentClass' doesn't implement Response interface.");
        }

        $this->initializeComponentKey($contentKey);

        return $content;
    }

    protected function initializeComponentKey(string $contentKey): void
    {
        if (!array_key_exists($contentKey, $this->content)) {
            $this->content[$contentKey] = [];
        }
    }

    public function append(string $key, ResponseInterface $content): void
    {
        $this->content[$key] = array_merge($this->content[$key], $content->toSchema());
    }

    public function toSchema(): array
    {
        return ['content' => $this->content];
    }
}
