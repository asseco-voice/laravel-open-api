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

        $reflection = new ReflectionClass($route->controllerName());
        $classDocBlock = $reflection->getDocComment();
        $operations = $route->operations();

        $parameters = $route->hasPathParameters() ? $route->getPathParameters() : [];

        // This must come after getting parameters or it won't catch whether the path parameter is required or not
        $routePath = str_replace('?', '', $routePath);

        $this->schemaBuilder->initExtractor($route->controllerName());
        $this->schemaBuilder->generateComponents();
        $this->schemaBuilder->addUriPath($routePath);

        foreach ($operations as $operation) {

            $methodDocBlock = new DocBlock($reflection->getMethod($route->controllerMethod())->getDocComment());
            $this->schemaBuilder->generateOperations($operation, $methodDocBlock, $routePath);
            $this->schemaBuilder->generateParameters($routePath, $operation, $parameters);
            $this->schemaBuilder->generateResponses($routePath, $operation);
        }
    }
}
