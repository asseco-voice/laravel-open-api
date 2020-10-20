<?php

declare(strict_types=1);

namespace Voice\OpenApi\Specification\Components;

use Voice\OpenApi\Contracts\Serializable;
use Voice\OpenApi\Exceptions\OpenApiException;
use Voice\OpenApi\Specification\Components\Parts\Component;
use Voice\OpenApi\Specification\Components\Parts\Schemas;

class Components implements Serializable
{
    protected array $components = [];

    protected array $registered = [
        'schemas' => Schemas::class,
    ];

    public function __construct()
    {
        foreach ($this->registered as $componentKey => $componentClass) {
            $this->initializeComponentKey($componentKey);
        }
    }

    protected function initializeComponentKey(string $componentKey): void
    {
        if (!array_key_exists($componentKey, $this->components)) {
            $this->components[$componentKey] = [];
        }
    }

    /**
     * @param Component $component
     * @throws OpenApiException
     */
    public function append(?Component $component): void
    {
        if (!$component) {
            return;
        }

        $componentClass = get_class($component);

        if (!in_array($componentClass, $this->registered)) {
            throw new OpenApiException('Class you are trying to append is not registered.');
        }

        $key = array_search($componentClass, $this->registered);

        $this->components[$key] = array_merge($this->components[$key], $component->toSchema());
    }

    public function toSchema(): array
    {
        return ['components' => $this->components];
    }
}
