<?php

namespace Voice\OpenApi\Specification\Parts;

use Mpociot\Reflection\DocBlock;
use ReflectionClass;
use Voice\OpenApi\Contracts\Serializable;
use Voice\OpenApi\Extractor;
use Voice\OpenApi\RouteWrapper;
use Voice\OpenApi\Traits\MergesArrays;

class Path implements Serializable
{
    use MergesArrays;

    protected array $operations = [];

    private RouteWrapper $route;
    private Extractor $extractor;

    public function __construct(RouteWrapper $route, Extractor $extractor)
    {
        $this->route = $route;
        $this->extractor = $extractor;
    }

    public function append(Operation $operation)
    {
        // + will overwrite same array keys.
        // This is okay, operations are unique for a single route.
        $this->operations += $operation->toSchema();
    }

    public function toSchema(): array
    {
        return [$this->route->path() => $this->operations];
    }

    public function generateOperation()
    {
        $routeOperations = $this->route->operations();
        $parameters = $this->route->getPathParameters();

        $reflection = new ReflectionClass($this->route->controllerName());
        $methodDocBlock = new DocBlock($reflection->getMethod($this->route->controllerMethod())->getDocComment());

        foreach ($routeOperations as $routeOperation) {

            $operation = new Operation($this->extractor, $methodDocBlock, $routeOperation);

            $operation->generateResponses();
            $operation->generateParameters($parameters);

            $this->append($operation);
        }
    }
}
