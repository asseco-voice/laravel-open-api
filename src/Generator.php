<?php

namespace Voice\OpenApi;

use Illuminate\Routing\RouteCollection;
use Illuminate\Routing\Router;
use Mpociot\Reflection\DocBlock;
use ReflectionClass;

class Generator
{
    protected RouteCollection    $routes;
    private OpenApiSchemaBuilder $schemaBuilder;

    public function __construct(Router $router, OpenApiSchemaBuilder $schemaBuilder)
    {
        $this->routes = $router->getRoutes();
        $this->schemaBuilder = $schemaBuilder;
    }

    public function generate(): array
    {
        foreach ($this->routes as $route) {

            $routeName = $route->getName();
            if (!$routeName || !(preg_match('/containers/', $routeName))) {
                continue;
            }

            $this->generateRouteDocumentation(new RouteWrapper($route));
        }

        return $this->schemaBuilder->document;
    }

    protected function generateRouteDocumentation(RouteWrapper $route): void
    {
        if ($route->isClosure()) {
            echo "Skipping {$route->uri()}, closure routes not supported.\n";
            return;
        }

        $this->schemaBuilder->initExtractor($route->controllerName());
        $this->schemaBuilder->generateComponents();
        $this->schemaBuilder->addUriPath($route->uri());

        $reflection = new ReflectionClass($route->controllerName());
        $classDocBlock = $reflection->getDocComment();
        $requestMethods = $route->requestMethods();

        foreach ($requestMethods as $requestMethod) {
            $methodDocBlock = new DocBlock($reflection->getMethod($route->controllerMethod())->getDocComment());
            $this->schemaBuilder->generateOperations($requestMethod, $methodDocBlock, $route->uri());
        }
    }
}
