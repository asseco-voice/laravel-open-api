<?php

namespace Voice\OpenApi\Specification\Factories;

use Mpociot\Reflection\DocBlock;
use ReflectionClass;
use Voice\OpenApi\Extractor;
use Voice\OpenApi\RouteWrapper;
use Voice\OpenApi\Specification\Parameters;
use Voice\OpenApi\Specification\Parts\DataTypes\Integer;
use Voice\OpenApi\Specification\Parts\Operation;
use Voice\OpenApi\Specification\Parts\Parameters\PathParameter;
use Voice\OpenApi\Specification\Parts\Path;
use Voice\OpenApi\Specification\Parts\Response;
use Voice\OpenApi\Specification\Parts\ResponseContent;
use Voice\OpenApi\Specification\Paths;
use Voice\OpenApi\Specification\Responses;

class PathsFactory
{
    private RouteWrapper $route;
    private Extractor $extractor;

    public Paths $paths;

    public function __construct()
    {
        $this->paths = new Paths();
    }

    public function generate(RouteWrapper $routeWrapper, Extractor $extractor)
    {
        $this->route = $routeWrapper;
        $this->extractor = $extractor;

        $path = $this->generatePathOperations();

        $this->paths->append($path);
    }

    protected function generatePathOperations(): Path
    {
        $path = new Path($this->route->path());

        $reflection = new ReflectionClass($this->route->controllerName());
        $methodDocBlock = new DocBlock($reflection->getMethod($this->route->controllerMethod())->getDocComment());

        $operations = $this->route->operations();
        $parameters = $this->route->getPathParameters();

        foreach ($operations as $operation) {
            $operation = $this->generateOperation($operation, $methodDocBlock, $parameters);
            $path->append($operation);
        }

        return $path;
    }

    public function generateOperation(string $operation, DocBlock $methodDocBlock, array $parameters)
    {
        $content = new ResponseContent($this->extractor->oneWordNamespacedModel());

        // TODO: remove hard coding
        $response = new Response('200', 'Successful request');
        $response->appendContent($content);

        $responses = new Responses();
        $responses->append($response);

        $operationOptions = [
            'summary'     => $methodDocBlock->getShortDescription(),
            'description' => $methodDocBlock->getLongDescription()->getContents(),
            'tags'        => [
                $this->extractor->groupTag
            ],
        ];

        $operationBlock = new Operation($operation, $responses, $operationOptions);

        $parameters = $this->generateParameters($parameters);

        if ($parameters) {
            $operationBlock->appendParameters($parameters);
        }

        return $operationBlock;
    }


    public function generateParameters(array $parameters)
    {
        if (empty($parameters)) {
            return [];
        }

        $parameterBlock = new Parameters();

//        // TODO: guess with multi parameters
//        if ($this->extractor->model) {
//            $keyName = (new $this->extractor->model)->getRouteKeyName();
//            $type = $this->extractor->getTypeForColumn($keyName);
//        }

        foreach ($parameters as $parameter) {

            // TODO: remove hard coding
            $dataType = new Integer();
            $parameterType = new PathParameter($parameter['name'], $dataType);

            $parameterBlock->append($parameterType);
        }

        return $parameterBlock;
    }
}
