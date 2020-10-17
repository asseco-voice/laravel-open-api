<?php

namespace Voice\OpenApi;

use Illuminate\Database\Eloquent\Model;
use Voice\OpenApi\Specification\Components\Parts\Schemas;
use Voice\OpenApi\Specification\Paths\Operations\Operation;
use Voice\OpenApi\Specification\Paths\Operations\RequestBody;
use Voice\OpenApi\Specification\Paths\Operations\Responses;
use Voice\OpenApi\Specification\Paths\Path;

class Extractor
{
    protected const DEFAULT_NAMESPACE = 'App';

    public ?Model $model;
    public Path $path;

    public ?Schemas $requestSchemas = null;
    public ?Schemas $responseSchemas = null;

    protected RouteWrapper $route;
    protected string $controller;
    protected string $method;
    protected string $candidate;
    protected string $namespace;

    /**
     * Extractor constructor.
     * @param RouteWrapper $route
     * @throws Exceptions\OpenApiException
     * @throws \ReflectionException
     */
    public function __construct(RouteWrapper $route)
    {
        $this->route = $route;
        $this->controller = $route->controllerName();
        $this->method = $route->controllerMethod();
        $this->namespace = $this->guessNamespace();
        $this->candidate = $this->getModelCandidate();
        $this->path = new Path($route->path());
    }

    public function extract()
    {
        $reflectionExtractor = new ReflectionExtractor($this->controller, $this->method);

        $this->model = $reflectionExtractor->getModel($this->namespace, $this->candidate);
        $pathParameters = $reflectionExtractor->getPathParameters($this->route->getPathParameters());
        $methodData = $reflectionExtractor->getMethodData($this->candidate);

        $this->requestSchemas = new Schemas();
        $this->responseSchemas = new Schemas();

        $routeOperations = $this->route->operations();

        foreach ($routeOperations as $routeOperation) {

            $operation = new Operation($methodData, $routeOperation);

            $responses = $this->generateResponses($reflectionExtractor, $routeOperation);
            $operation->appendResponses($responses);

            $requestBody = $this->generateRequest($reflectionExtractor, $routeOperation);

            if ($requestBody) {
                $operation->appendRequestBody($requestBody);
            }

            if ($pathParameters) {
                $operation->appendParameters($pathParameters);
            }

            $this->path->append($operation);
        }
    }

    /**
     * Try to guess model namespace from controller, assuming App part of the
     * namespace exist.
     *
     * I.e.
     *
     * My\Namespace\App\Http\Controllers\MyController will return My\Namespace\App\
     *
     * My\Non\Laravel\Namespace\MyController will return App\
     *
     * @param string $controller
     * @return string
     */
    public function guessNamespace(): string
    {
        $split = preg_split('|' . self::DEFAULT_NAMESPACE . '|', $this->controller);

        if (count($split) < 2) {
            return self::DEFAULT_NAMESPACE . '\\';
        }

        return $split[0] . self::DEFAULT_NAMESPACE . '\\';
    }

    /**
     * Parse possible model name from controller.
     * At this point we still don't know if this class exists or not.
     *
     * @param string $controller
     * @return string
     */
    protected function getModelCandidate(): string
    {
        $split = explode('\\', $this->controller);
        $controllerName = end($split);

        return str_replace('Controller', '', $controllerName);
    }

    public function concatModelName(): string
    {
        $controller = str_replace([$this->namespace, 'Http\\Controllers\\', '\\', ' '], '', $this->controller);
        $namespace = str_replace(['\\', ' '], '', $this->namespace);

        $prefix = "{$this->method}_{$namespace}_{$controller}_";

        if (!$this->model) {
            return $prefix . $this->candidate;
        }

        $model = str_replace(['\\', ' '], '', get_class($this->model));

        return $prefix . str_replace($namespace, '', $model);
    }

    public function prependModelName(string $prefix)
    {
        return "{$prefix}_{$this->concatModelName()}";
    }

    protected function generateResponses(ReflectionExtractor $reflectionExtractor, $routeOperation): Responses
    {
        $responseGenerator = new ResponseGenerator($reflectionExtractor);

        $responseModelName = $this->prependModelName("Response");

        $responseSchema = $responseGenerator->createSchema($responseModelName, $this->model);
        $this->responseSchemas->append($responseSchema);

        return $responseGenerator->generate($responseModelName, $routeOperation, $this->route->hasPathParameters());
    }

    protected function generateRequest(ReflectionExtractor $reflectionExtractor, $routeOperation): ?RequestBody
    {
        $requestGenerator = new RequestGenerator($reflectionExtractor);
        $requestModelName = $this->prependModelName("Request");

        $requestSchema = $requestGenerator->createSchema($requestModelName, $this->model);

        if ($requestSchema && in_array($routeOperation, ['post', 'put', 'patch'])) {

            $this->requestSchemas->append($requestSchema);

            return $requestGenerator->getBody($requestModelName);
        }

        return null;
    }
}
