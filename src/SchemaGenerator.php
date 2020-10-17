<?php

namespace Voice\OpenApi;

use Illuminate\Routing\RouteCollection;
use Illuminate\Routing\Router;
use Voice\OpenApi\Specification\Components\Components;
use Voice\OpenApi\Specification\Document;
use Voice\OpenApi\Specification\Paths\Paths;

class SchemaGenerator
{
    protected RouteCollection $routerRoutes;
    public Document $document;

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
//            if (!$routeName || !(preg_match('/containers\./', $routeName))) {
//                continue;
//            }

            $route = new RouteWrapper($routerRoute);

            if ($route->shouldSkip()) {
                continue;
            }

            $extractor = new Extractor($route);
            $extractor->extract();

            $paths->append($extractor->path);

            if ($extractor->requestSchemas) {
                $components->append($extractor->requestSchemas);
            }

            $components->append($extractor->responseSchemas);
        }

        $this->document->appendPaths($paths);
        $this->document->appendComponents($components);
    }
}
