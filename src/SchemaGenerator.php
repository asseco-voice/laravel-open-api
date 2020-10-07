<?php

namespace Voice\OpenApi;

use Illuminate\Routing\RouteCollection;
use Illuminate\Routing\Router;
use Voice\OpenApi\Specification\Document;
use Voice\OpenApi\Specification\Factories\ComponentsFactory;
use Voice\OpenApi\Specification\Factories\PathsFactory;
use Voice\OpenApi\Specification\Parts\Components\Schemas;
use Voice\OpenApi\Specification\Parts\Models\Properties;

class SchemaGenerator
{
    protected RouteCollection $routes;
    public Document $document;
    public Extractor $extractor;

    public function __construct(Router $router)
    {
        $this->document = new Document();
        $this->routes = $router->getRoutes();
    }

    public function generate(): array
    {
        $pathsFactory = new PathsFactory();

        foreach ($this->routes as $route) {

            // Testing purposes only
//            $routeName = $route->getName();
//            if (!$routeName || !(preg_match('/authorization-rules\./', $routeName))) {
//                continue;
//            }

            $routeWrapper = new RouteWrapper($route);

            if ($routeWrapper->shouldSkip()) {
                continue;
            }

            $this->extractor = new Extractor($routeWrapper->controllerName());

            $pathsFactory->generate($routeWrapper, $this->extractor);

            $properties = new Properties($this->extractor->modelColumns());


            $model = $this->extractor->oneWordNamespacedModel();

            if (!$model) {
                continue;
            }

            $schema = new Schemas($model, $properties);

            $componentFactory = new ComponentsFactory($schema, $routeWrapper, $this->extractor);

            $this->document->appendComponents($componentFactory->generate());
        }

        $this->document->appendPaths($pathsFactory->paths);

        return $this->document->toSchema();
    }

    public function getTags()
    {
//        foreach ($methodDocBlock->getTags() as $tag) {
//            echo print_r($tag->getName(), true) . "\n";
//        }
    }
}
