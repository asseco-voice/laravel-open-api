<?php

namespace Voice\OpenApi\Handlers;

use Voice\OpenApi\Exceptions\OpenApiException;
use Voice\OpenApi\Specification\Paths\Operations\Parameters\DataTypes\DataType;
use Voice\OpenApi\Specification\Paths\Operations\Parameters\Parameters;
use Voice\OpenApi\Specification\Paths\Operations\Parameters\PathParameter;

class PathHandler extends AbstractHandler
{
    public function handle(array $pathParameters): ?Parameters
    {
        $parameters = new Parameters();

        if (!$this->tags) {
            if (!$pathParameters) {
                return null;
            }

            foreach ($pathParameters as $pathParameter) {
                $parameters->append($pathParameter);
            }

            return $parameters;
        }

        foreach ($this->tags as $methodParameter) {
            $split = explode(' ', $methodParameter, 3);
            $count = count($split);

            if ($count < 2) {
                throw new OpenApiException("Wrong number of path parameters provided");
            }

            $name = $split[0];
            $type = DataType::getMappedClass($split[1]);
            $description = ($count >= 3) ? $split[2] : '';

            $parameter = new PathParameter($name, $type);
            $parameter->addDescription($description);

            $parameters->append($parameter);
        }

        return $parameters;
    }
}
