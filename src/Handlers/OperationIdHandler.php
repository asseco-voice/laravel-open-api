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
     * @return string
     * @throws OpenApiException
     */
    public static function handle(array $tags, string $candidate, string $method, string $operation): string
    {
        if (!$tags) {
            return self::generateOperationId($method, $operation, $candidate);
        }

        $tag = $tags[0];
        $split = explode(' ', $tag);

        self::verifyParameters(count($split));

        return self::generateOperationId($method, $operation, $split[0]);
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
    protected static function generateOperationId(string $method, string $operation, string $candidate): string
    {
        switch ($method) {
            case 'index':
                return 'getAll' . Str::plural($candidate);
            case 'store':
                return 'create' . $candidate;
            case 'show':
                return 'get' . $candidate;
            case 'update':
                return $operation . $candidate;
            case 'destroy':
                return 'delete' . $candidate;
            default:
                return $method . $candidate;
        }
    }

}
