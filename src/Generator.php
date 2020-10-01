<?php

namespace Voice\OpenApi;

use Illuminate\Routing\Route;
use Illuminate\Routing\RouteCollection;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Config;

class Generator
{
    protected RouteCollection $routes;
    protected SchemaBuilder   $schemaBuilder;
    protected array           $excludeRules;
    protected bool            $verbose;

    public function __construct(Router $router, SchemaBuilder $schemaBuilder)
    {
        $this->routes = $router->getRoutes();
        $this->schemaBuilder = $schemaBuilder;
        $this->excludeRules = Config::get('asseco-open-api.exclude');
        $this->verbose = Config::get('asseco-open-api.verbose');
    }

    public function generate(): array
    {
        foreach ($this->routes as $route) {

            if ($this->shouldExclude($route)) {
                continue;
            }

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
            if ($this->verbose) echo "Skipping {$routePath}, closure routes not supported.\n";
            return;
        }

        $operations = $route->operations();
        $parameters = $route->hasPathParameters() ? $route->getPathParameters() : [];

        $this->schemaBuilder->generate($route->controllerName(), $route->controllerMethod(), $routePath, $operations, $parameters);
    }

    protected function shouldExclude(Route $route): bool
    {
        $byName = $this->excludeRules['route_name'];

        foreach ($byName as $name) {
            if ($route->getName() && (preg_match('/' . $name . '/', $route->getName()))) {
                if ($this->verbose) echo "Excluding route by name: '{$route->getName()}'\n";
                return true;
            }
        }

        $byController = $this->excludeRules['controller_name'];

        foreach ($byController as $controller) {
            $controllerClass = get_class($route->getController());

            if ($controller === $controllerClass) {
                if ($this->verbose) echo "Excluding route by controller: '{$controllerClass}'\n";
                return true;
            }
        }

        return false;
    }
}
