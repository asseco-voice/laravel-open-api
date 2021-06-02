<?php

namespace Asseco\OpenApi\Handlers;

use Asseco\OpenApi\Exceptions\OpenApiException;
use Asseco\OpenApi\Specification\Paths\Operations\Parameters\DataTypes\DataType;
use Asseco\OpenApi\Specification\Paths\Operations\Parameters\Parameters;
use Asseco\OpenApi\Specification\Paths\Operations\Parameters\PathParameter;

class PathHandler extends AbstractHandler
{
    /**
     * @param array $tags
     * @param array $pathParameters
     * @return Parameters|null
     * @throws OpenApiException
     */
    public static function handle(array $tags, array $pathParameters): ?Parameters
    {
        $parameters = new Parameters();

        if (!$tags) {
            if (!$pathParameters) {
                return null;
            }

            foreach ($pathParameters as $pathParameter) {
                $parameters->append($pathParameter);
            }

            return $parameters;
        }

        foreach ($tags as $methodParameter) {
            $split = explode(' ', $methodParameter, 3);
            $count = count($split);

            self::verifyParameters($count);

            [$name, $type, $description] = self::parseTag($split, $count);

            $parameter = self::createParameter($name, $type, $description);

            $parameters->append($parameter);
        }

        return $parameters;
    }

    /**
     * @param int $count
     * @throws OpenApiException
     */
    protected static function verifyParameters(int $count): void
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
    protected static function parseTag(array $split, int $count): array
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
    protected static function createParameter($name, $type, $description): PathParameter
    {
        $parameter = new PathParameter($name, $type);
        $parameter->addDescription($description);

        return $parameter;
    }
}
