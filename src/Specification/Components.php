<?php

namespace Voice\OpenApi\Specification;

use Voice\OpenApi\Contracts\Serializable;
use Voice\OpenApi\Exceptions\OpenApiException;
use Voice\OpenApi\Extractor;
use Voice\OpenApi\Specification\Parts\Components\Components as ComponentsInterface;
use Voice\OpenApi\Specification\Parts\Components\Schemas;
use Voice\OpenApi\Traits\MergesArrays;

class Components implements Serializable
{
    use MergesArrays;

    protected array $components = [];

    protected array $registered = [
        'schemas' => Schemas::class
    ];

    public function generateComponents(Extractor $extractor): void
    {
        foreach ($this->registered as $componentKey => $componentClass) {
            $component = $this->initializeComponent($componentKey, $componentClass);

//            if ($this->modelAlreadyResolved($name, $componentKey)) {
//                continue;
//            }

            $component->generate($extractor);
            $this->append($componentKey, $component);
        }
    }

    protected function initializeComponent(string $componentKey, string $componentClass): ComponentsInterface
    {
        $component = new $componentClass();

        if (!$component instanceof ComponentsInterface) {
            throw new OpenApiException("Component '$componentClass' doesn't implement Components interface.");
        }

        $this->initializeComponentKey($componentKey);

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

    public function append(string $key, ComponentsInterface $component): void
    {
        $this->components[$key] = array_merge($this->components[$key], $component->toSchema());
    }

    public function toSchema(): array
    {
        return ['components' => $this->components];
    }
}
