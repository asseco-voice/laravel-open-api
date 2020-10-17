<?php

namespace Voice\OpenApi;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Routing\RouteCollection;
use Illuminate\Routing\Router;
use ReflectionException;
use Voice\OpenApi\Guessers\CandidateGuesser;
use Voice\OpenApi\Guessers\NamespaceGuesser;
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

    /**
     * @return array
     * @throws Exceptions\OpenApiException
     * @throws ReflectionException
     */
    public function generate(): array
    {
        [$paths, $components] = $this->traverseRoutes();

        $this->document->appendPaths($paths);
        $this->document->appendComponents($components);

        return $this->document->toSchema();
    }

    /**
     * @return array
     * @throws Exceptions\OpenApiException
     * @throws ReflectionException
     */
    protected function traverseRoutes(): array
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
            $namespace = $this->guessNamespace($controller);
            $candidate = $this->guessCandidate($controller);

            $extractor = new Extractor($controller, $method);

            $model = $extractor->getModel($namespace, $candidate);

            $pathParameters = $extractor->getPathParameters($route->getPathParameters());
            $methodData = $extractor->getMethodData($candidate);

            $path = new Path($route->path());

            $requestSchemas = new Schemas();
            $responseSchemas = new Schemas();

            $schemaName = $this->schemaName($namespace, $controller, $method, $candidate, $model);

            $routeOperations = $route->operations();

            foreach ($routeOperations as $routeOperation) {

                $operation = new Operation($methodData, $routeOperation);

                $responseGenerator = new ResponseGenerator($extractor);
                $responseModelName = "Response" . $schemaName;
                $responseSchema = $responseGenerator->createSchema($responseModelName, $model);

                $responseSchemas->append($responseSchema);

                $responses = $responseGenerator->generate($responseModelName, $routeOperation, $route->hasPathParameters());

                $operation->appendResponses($responses);


                $requestGenerator = new RequestGenerator($extractor);
                $requestModelName = "Request" . $schemaName;
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

        return [$paths, $components];
    }

    protected function guessNamespace(string $controller): string
    {
        return (new NamespaceGuesser())($controller);
    }

    protected function guessCandidate(string $controller)
    {
        return (new CandidateGuesser())($controller);
    }

    public function schemaName(string $namespace, string $controller, string $method, string $candidate, ?Model $model): string
    {
        $joinedNamespace = $this->removeSlashes($namespace);
        $joinedController = $this->removeSlashes($controller);

        $finalController = str_replace([$joinedNamespace, 'Http\\Controllers\\'], '', $joinedController);

        $prefix = "{$method}_{$joinedNamespace}_{$finalController}_";

        if (!$model) {
            return $prefix . $candidate;
        }

        $joinedModel = $this->removeSlashes(get_class($model));

        $modelName = str_replace(['\\', ' '], '', $joinedModel);

        return $prefix . str_replace($joinedNamespace, '', $modelName);
    }

    protected function removeSlashes(string $input)
    {
        return str_replace(['\\', ' '], '', $input);
    }
}
