<?php

namespace Voice\OpenApi;

use Illuminate\Routing\RouteCollection;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Mpociot\Reflection\DocBlock;
use ReflectionClass;

class Generator
{
    public const CACHE_PREFIX = 'open_api_extractor_';

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

//            $routeName = $route->getName();
//            if (!$routeName || !(preg_match('/\.media\./', $routeName))) {
//                continue;
//            }

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

        $extractor = $this->initExtractor($route);

        $groupTag = $extractor->groupTag;
        $namespacedModel = $extractor->oneWordNamespacedModel();
        $modelColumns = $extractor->modelColumns();

        $this->schemaBuilder->generateComponents($namespacedModel, $modelColumns);
        $this->schemaBuilder->addUriPath($route->uri());

        $this->generateMethodDocumentation($route, $extractor, $groupTag, $namespacedModel);
    }

    protected function initExtractor(RouteWrapper $route)
    {
        $cacheKey = self::CACHE_PREFIX . $route->controllerName();

        if (Cache::has($cacheKey) && !Config::get('asseco-open-api.bust_cache')) {
            echo "caching extractor \n";
            return Cache::get($cacheKey);
        }

        $extractor = new Extractor($route->controllerName());
        Cache::put($cacheKey, $extractor, 60 * 60 * 24);

        return $extractor;
    }

    protected function generateMethodDocumentation(RouteWrapper $route, Extractor $extractor, $tagName, $snakeClassName): void
    {
        $reflection = new ReflectionClass($route->controllerName());
        $classDocBlock = $reflection->getDocComment();
        $requestMethods = $route->requestMethods();

        foreach ($requestMethods as $requestMethod) {
            $methodDocBlock = new DocBlock($reflection->getMethod($route->controllerMethod())->getDocComment());
            $this->schemaBuilder->generateDoc($requestMethod, $methodDocBlock, $extractor->model ?: $tagName, $snakeClassName, $route->uri());
        }
    }
}
