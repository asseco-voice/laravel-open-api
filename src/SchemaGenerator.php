<?php

namespace Voice\OpenApi;

use Illuminate\Routing\RouteCollection;
use Illuminate\Routing\Router;
use Voice\OpenApi\Specification\Components\Components;
use Voice\OpenApi\Specification\Components\Parts\Schemas;
use Voice\OpenApi\Specification\Document;
use Voice\OpenApi\Specification\Paths\Operations\Operation;
use Voice\OpenApi\Specification\Paths\Path;
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

            $controller = $route->controllerName();
            $method = $route->controllerMethod();

            $nameExtractor = new NameExtractor($controller, $method);

            $reflectionExtractor = new ReflectionExtractor($controller, $method);

            $model = $reflectionExtractor->getModel($nameExtractor->namespace, $nameExtractor->candidate);

            $pathParameters = $reflectionExtractor->getPathParameters($route->getPathParameters());
            $methodData = $reflectionExtractor->getMethodData($nameExtractor->candidate);

            $path = new Path($route->path());

            $requestSchemas = new Schemas();
            $responseSchemas = new Schemas();

            $routeOperations = $route->operations();

            foreach ($routeOperations as $routeOperation) {

                $operation = new Operation($methodData, $routeOperation);

                $responseGenerator = new ResponseGenerator($reflectionExtractor);
                $responseModelName = $nameExtractor->prependModelName("Response", $model);

                $responseSchema = $responseGenerator->createSchema($responseModelName, $model);
                $responseSchemas->append($responseSchema);

                $responses = $responseGenerator->generate($responseModelName, $routeOperation, $route->hasPathParameters());

                $operation->appendResponses($responses);


                $requestGenerator = new RequestGenerator($reflectionExtractor);
                $requestModelName = $nameExtractor->prependModelName("Request", $model);

                $requestSchema = $requestGenerator->createSchema($requestModelName, $model);

                $requestBody = null;
                if ($requestSchema && in_array($routeOperation, ['post', 'put', 'patch'])) {
                    $requestSchemas->append($requestSchema);
                    $requestBody = $requestGenerator->getBody($requestModelName);
                }

                if ($requestBody) {
                    $operation->appendRequestBody($requestBody);
                }

                if ($pathParameters) {
                    $operation->appendParameters($pathParameters);
                }

                $path->append($operation);
            }


            $paths->append($path);

            if ($requestSchemas) {
                $components->append($requestSchemas);
            }

            $components->append($responseSchemas);
        }

        $this->document->appendPaths($paths);
        $this->document->appendComponents($components);
    }
}
