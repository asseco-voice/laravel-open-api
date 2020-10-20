<?php

declare(strict_types=1);

namespace Voice\OpenApi\Specification\Shared\Content;

use Voice\OpenApi\Contracts\Serializable;
use Voice\OpenApi\Exceptions\OpenApiException;

class Content implements Serializable
{
    protected array $content = [];

    protected array $registered = [
        'application/json' => JsonSchema::class,
    ];

    public function __construct()
    {
        foreach ($this->registered as $contentKey => $contentClass) {
            $this->initializeComponentKey($contentKey);
        }
    }

    protected function initializeComponentKey(string $contentKey): void
    {
        if (!array_key_exists($contentKey, $this->content)) {
            $this->content[$contentKey] = [];
        }
    }

    /**
     * @param ContentSchema $content
     * @throws OpenApiException
     */
    public function append(ContentSchema $content): void
    {
        $contentClass = get_class($content);

        if (!in_array($contentClass, $this->registered)) {
            throw new OpenApiException('Class you are trying to append is not registered.');
        }

        $key = array_search($contentClass, $this->registered);

        $this->content[$key] = array_merge($this->content[$key], $content->toSchema());
    }

    public function toSchema(): array
    {
        return ['content' => $this->content];
    }
}
