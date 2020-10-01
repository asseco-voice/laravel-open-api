<?php

namespace Voice\OpenApi;

use Illuminate\Routing\RouteCollection;
use Illuminate\Routing\Router;

class Generator
{
    protected RouteCollection    $routes;
    private SchemaBuilder $schemaBuilder;

    public function __construct(Router $router, SchemaBuilder $schemaBuilder)
    {
        $this->routes = $router->getRoutes();
        $this->schemaBuilder = $schemaBuilder;
    }

    public function generate(): array
    {
        foreach ($this->routes as $route) {
//
//            $routeName = $route->getName();
//            if (!$routeName || !(preg_match('/custom-field\.plain/', $routeName))) {
//                continue;
//            }

            $this->generateRouteDocumentation(new RouteWrapper($route));
        }

        return $this->schemaBuilder->document;
    }

    protected function generateRouteDocumentation(RouteWrapper $route): void
    {
        $routePath = $route->path();

        if ($route->isClosure()) {
            echo "Skipping {$routePath}, closure routes not supported.\n";
            return;
        }

        $operations = $route->operations();
        $parameters = $route->hasPathParameters() ? $route->getPathParameters() : [];

        $this->schemaBuilder->generate($route->controllerName(), $route->controllerMethod(), $routePath, $operations, $parameters);
    }
}
