<?php

namespace Asseco\OpenApi;

use Asseco\OpenApi\Guessers\CandidateGuesser;
use Asseco\OpenApi\Guessers\NamespaceGuesser;
use Asseco\OpenApi\Specification\Components\Components;
use Asseco\OpenApi\Specification\Components\Parts\Schemas;
use Asseco\OpenApi\Specification\Document;
use Asseco\OpenApi\Specification\Paths\Operations\Operation;
use Asseco\OpenApi\Specification\Paths\Path;
use Asseco\OpenApi\Specification\Paths\Paths;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Routing\RouteCollection;
use Illuminate\Routing\Router;
use ReflectionException;

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
//            if (!$routeName || !(preg_match('/custom-field\.remote\.store/', $routeName))) {
//                continue;
//            }

            $route = new RouteWrapper($routerRoute);

            if ($route->shouldSkip()) {
                continue;
            }

            [$tagExtractor, $model, $methodData, $pathParameters, $schemaName, $namespace] =
                $this->initialize($route);

            [$path, $requestSchemas, $responseSchemas] =
                $this->traverseOperations($route, $methodData, $tagExtractor, $schemaName, $model, $pathParameters, $namespace);

            $paths->append($path);

            $components->append($requestSchemas);
            $components->append($responseSchemas);
        }

        return [$paths, $components];
    }

    /**
     * @param RouteWrapper $route
     * @return array
     * @throws Exceptions\OpenApiException
     * @throws ReflectionException
     */
    protected function initialize(RouteWrapper $route): array
    {
        $controller = $route->controllerName();
        $method = $route->controllerMethod();
        $namespace = (new NamespaceGuesser())($controller);
        $candidate = (new CandidateGuesser())($controller);

        $tagExtractor = new TagExtractor($controller, $method);
        $model = $tagExtractor->getModel($namespace, $candidate);
        $methodData = $tagExtractor->getMethodData($candidate);
        $pathParameters = $tagExtractor->getPathParameters($route->getPathParameters());

        $schemaName = $this->schemaName($namespace, $controller, $method, $candidate, $model);

        return [$tagExtractor, $model, $methodData, $pathParameters, $schemaName, $namespace];
    }

    /**
     * @param RouteWrapper $route
     * @param $methodData
     * @param $tagExtractor
     * @param $schemaName
     * @param $model
     * @param $pathParameters
     * @param string $namespace
     * @return array
     * @throws Exceptions\OpenApiException
     */
    protected function traverseOperations(RouteWrapper $route, $methodData, $tagExtractor, $schemaName, $model, $pathParameters, string $namespace): array
    {
        $path = new Path($route->path());
        $requestSchemas = new Schemas();
        $responseSchemas = new Schemas();

        foreach ($route->operations() as $routeOperation) {
            $operation = new Operation($methodData, $routeOperation);

            [$responseSchema, $responses] =
                $this->generateResponses($tagExtractor, $schemaName, $routeOperation, $route->hasPathParameters(), $model);

            $requestGenerator = new RequestGenerator($tagExtractor, 'Request_' . $schemaName);
            $requestSchema = $requestGenerator->createSchema($namespace, $model);

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

        return [$path, $requestSchemas, $responseSchemas];
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
        $responseGenerator = new ResponseGenerator($extractor, 'Response_' . $schemaName);

        $responseSchema = $responseGenerator->createSchema($model);
        $responses = $responseGenerator->generate($routeOperation, $routeHasPathParameters, !empty($responseSchema));

        return [$responseSchema, $responses];
    }
}
