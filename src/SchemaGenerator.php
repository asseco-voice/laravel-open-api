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
            $namespace = (new NamespaceGuesser())($controller);
            $candidate = (new CandidateGuesser())($controller);

            $extractor = new TagExtractor($controller, $method);
            $model = $extractor->getModel($namespace, $candidate);
            $methodData = $extractor->getMethodData($candidate);
            $pathParameters = $extractor->getPathParameters($route->getPathParameters());

            $schemaName = $this->schemaName($namespace, $controller, $method, $candidate, $model);

            $path = new Path($route->path());
            $requestSchemas = new Schemas();
            $responseSchemas = new Schemas();

            foreach ($route->operations() as $routeOperation) {

                $operation = new Operation($methodData, $routeOperation);

                [$responseSchema, $responses] =
                    $this->generateResponses($extractor, $schemaName, $routeOperation, $route->hasPathParameters(), $model);

                $requestGenerator = new RequestGenerator($extractor, "Request_" . $schemaName);
                $requestSchema = $requestGenerator->createSchema($model);

                $requestBody = null;
                if ($requestSchema && in_array($routeOperation, ['post', 'put', 'patch'])) {
                    $requestSchemas->append($requestSchema);
                    $requestBody = $requestGenerator->getBody();
                }

                $operation->appendRequestBody($requestBody);
                $operation->appendParameters($pathParameters);
                $operation->appendResponses($responses);

                $responseSchemas->append($responseSchema);

                $path->append($operation);
            }

            $paths->append($path);

            $components->append($requestSchemas);
            $components->append($responseSchemas);
        }

        return [$paths, $components];
    }

    public function schemaName(string $namespace, string $controller, string $method, string $candidate, ?Model $model): string
    {
        $joinedNamespace = $this->removeSlashes($namespace);
        $joinedController = $this->removeSlashes($controller);

        $finalController = str_replace([$joinedNamespace, 'HttpControllers'], '', $joinedController);

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

    protected function generateResponses(TagExtractor $extractor, string $schemaName, string $routeOperation, bool $routeHasPathParameters, ?Model $model): array
    {
        $responseGenerator = new ResponseGenerator($extractor, "Response_" . $schemaName);

        $responseSchema = $responseGenerator->createSchema($model);
        $responses = $responseGenerator->generate($routeOperation, $routeHasPathParameters);

        return [$responseSchema, $responses];
    }
}
