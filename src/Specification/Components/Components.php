<?php

namespace Voice\OpenApi\Specification\Components;

use Voice\OpenApi\Contracts\Serializable;
use Voice\OpenApi\Exceptions\OpenApiException;
use Voice\OpenApi\Specification\Components\Parts\Components as ComponentsInterface;
use Voice\OpenApi\Specification\Components\Parts\Schemas;
use Voice\OpenApi\Traits\MergesArrays;

class Components implements Serializable
{
    use MergesArrays;

    protected array $components = [];

    protected array $registered = [
        'schemas' => Schemas::class
    ];

    public function __construct()
    {
        foreach ($this->registered as $componentKey => $componentClass) {
            $this->initializeComponentKey($componentKey);
        }
    }

    protected function initializeComponent(string $componentClass): ComponentsInterface
    {
        $component = new $componentClass();

        if (!$component instanceof ComponentsInterface) {
            throw new OpenApiException("Component '$componentClass' doesn't implement Components interface.");
        }

        return $component;
    }

    protected function initializeComponentKey(string $componentKey): void
    {
        if (!array_key_exists($componentKey, $this->components)) {
            $this->components[$componentKey] = [];
        }
    }

    protected function modelAlreadyResolved(string $name, string $componentKey): bool
    {
        return in_array($name, $this->components[$componentKey]);
    }

    public function append(ComponentsInterface $component): void
    {
        $componentClass = get_class($component);

        if (!in_array($componentClass, $this->registered)) {
            throw new OpenApiException("Class you are trying to append is not registered.");
        }

        $key = array_search($componentClass, $this->registered);

        $this->components[$key] = array_merge($this->components[$key], $component->toSchema());
    }

    public function toSchema(): array
    {
        return ['components' => $this->components];
    }
}
