<?php

namespace Voice\OpenApi\Specification\Paths;

use Voice\OpenApi\Contracts\Serializable;
use Voice\OpenApi\Extractor;
use Voice\OpenApi\Specification\Paths\Operations\Operation;

class Path implements Serializable
{
    protected array $operations = [];

    private string $route;
    private Extractor $extractor;

    public function __construct(string $route)
    {
        $this->route = $route;
    }

    public function append(Operation $operation)
    {
        // + will overwrite same array keys.
        // This is okay, operations are unique for a single route.
        $this->operations += $operation->toSchema();
    }

    public function toSchema(): array
    {
        return [$this->route => $this->operations];
    }

    public function generateOperation()
    {
        $routeOperations = $this->route->operations();
        $pathParameters = $this->route->getPathParameters();

        foreach ($routeOperations as $routeOperation) {

            $operation = new Operation($routeOperation);

            $multiple = $routeOperation === 'get' && !$pathParameters;

            $operation->generateParameters();
            $operation->generateResponses($multiple);

//            if (in_array($routeOperation, ['post', 'put'])) {
//                $operation->generateRequestBody();
//            }

            $this->append($operation);
        }
    }
}
