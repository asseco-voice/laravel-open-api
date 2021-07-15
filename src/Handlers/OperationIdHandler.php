<?php

namespace Asseco\OpenApi\Handlers;

use Asseco\OpenApi\Exceptions\OpenApiException;
use Illuminate\Support\Str;

class OperationIdHandler extends AbstractHandler
{
    /**
     * @param array $tags
     * @param string $candidate
     * @param string $method
     * @param string $operation
     * @return mixed|string
     * @throws OpenApiException
     */
    public static function handle(array $tags, string $candidate, string $method, string $operation)
    {
        if (!$tags) {
            return self::generateOperationId($method, $operation, $candidate);
        }

        $tag = $tags[0];
        $split = explode(' ', $tag);

        self::verifyParameters(count($split));

        $operationId = $split[0];

        return $operationId;
    }

    /**
     * @param int $count
     * @throws OpenApiException
     */
    protected static function verifyParameters(int $count)
    {
        if ($count > 1) {
            throw new OpenApiException('Wrong number of parameters provided');
        }
    }


    /**
     * @param string $method
     * @param string $operation
     * @param string $candidate
     * @return string
     */
    protected static function generateOperationId(string $method, string $operation, string $candidate) : string
    {
        switch ($operation) {
            case 'get':
                if ($method == 'index') {
                    return 'getAll' . Str::plural($candidate);
                }
                return 'get' . $candidate . 'ByPrimaryKey';
            case 'post':
                return 'post' . $candidate;
            case 'put':
            case 'patch':
            case 'destroy':
                return $operation . $candidate . 'ByPrimaryKey';
            default:
                return $operation . $candidate;
        }
    }
}
