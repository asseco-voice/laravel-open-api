<?php

namespace Voice\OpenApi\Handlers;

use Voice\OpenApi\Exceptions\OpenApiException;
use Voice\OpenApi\Specification\Paths\Operations\Parameters\DataTypes\DataType;
use Voice\OpenApi\Specification\Paths\Operations\Parameters\Parameters;
use Voice\OpenApi\Specification\Paths\Operations\Parameters\PathParameter;

class PathHandler extends AbstractHandler
{
    /**
     * @param array $pathParameters
     * @return Parameters|null
     * @throws OpenApiException
     */
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

            $this->verifyParameters($count);

            [$name, $type, $description] = $this->parseTag($split, $count);

            $parameter = $this->createParameter($name, $type, $description);

            $parameters->append($parameter);
        }

        return $parameters;
    }

    /**
     * @param int $count
     * @throws OpenApiException
     */
    private function verifyParameters(int $count): void
    {
        if ($count < 2) {
            throw new OpenApiException('Wrong number of path parameters provided');
        }
    }

    /**
     * @param bool $split
     * @param int $count
     * @return array
     * @throws OpenApiException
     */
    private function parseTag(array $split, int $count): array
    {
        $name = $split[0];
        $type = DataType::getMappedClass($split[1]);
        $description = ($count >= 3) ? $split[2] : '';

        return [$name, $type, $description];
    }

    /**
     * @param $name
     * @param $type
     * @param $description
     * @return PathParameter
     */
    private function createParameter($name, $type, $description): PathParameter
    {
        $parameter = new PathParameter($name, $type);
        $parameter->addDescription($description);

        return $parameter;
    }
}
