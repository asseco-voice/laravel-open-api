<?php

namespace Voice\OpenApi\Specification\Factories;

use Voice\OpenApi\Extractor;
use Voice\OpenApi\RouteWrapper;
use Voice\OpenApi\Specification\Parts\Components\Components;

class ComponentsFactory
{
    private Components $components;
    private RouteWrapper $route;
    private Extractor $extractor;

    public function __construct(Components $components, RouteWrapper $routeWrapper, Extractor $extractor)
    {
        $this->components = $components;
        $this->route = $routeWrapper;
        $this->extractor = $extractor;
    }

    public function generate(): Components
    {
        return $this->components;
    }
}
