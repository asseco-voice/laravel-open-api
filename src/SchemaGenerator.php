<?php

namespace Voice\OpenApi;

use Illuminate\Routing\RouteCollection;
use Illuminate\Routing\Router;
use Voice\OpenApi\Specification\Components;
use Voice\OpenApi\Specification\Document;
use Voice\OpenApi\Specification\Parts\Components\Schema;
use Voice\OpenApi\Specification\Paths;

class SchemaGenerator
{
    protected RouteCollection $routerRoutes;
    public Document $document;
    public Extractor $extractor;

    public function __construct(Router $router, Document $document)
    {
        $this->document = $document;
        $this->routerRoutes = $router->getRoutes();
    }

    public function generate(): array
    {
        $this->traverseRoutes();

        return $this->document->toSchema();
    }

    protected function traverseRoutes()
    {
        $paths = new Paths();
        $components = new Components();

        foreach ($this->routerRoutes as $routerRoute) {

            // Testing purposes only
//            $routeName = $routerRoute->getName();
//            if (!$routeName || !(preg_match('/authorization-rules\./', $routeName))) {
//                continue;
//            }

            $route = new RouteWrapper($routerRoute);

            if ($route->shouldSkip()) {
                continue;
            }

            $this->extractor = new Extractor($route->controllerName());

            $paths->generatePath($route, $this->extractor);

            $model = $this->extractor->oneWordNamespacedModel();

            if (!$model) {
                continue;
            }

            $components->generateComponents($model, $this->extractor->modelColumns());
        }

        $this->document->appendPaths($paths);
        $this->document->appendComponents($components);
    }

    public function getTags()
    {
//        foreach ($methodDocBlock->getTags() as $tag) {
//            echo print_r($tag->getName(), true) . "\n";
//        }
    }
}
