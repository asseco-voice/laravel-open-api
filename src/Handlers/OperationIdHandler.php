<?php

namespace Asseco\OpenApi\Handlers;

use Asseco\OpenApi\Exceptions\OpenApiException;
use Illuminate\Support\Arr;
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

        return self::generateOperationId($method, $operation, Arr::get($split, '0', $candidate));
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
        $serviceName = config('asseco-open-api.prepend_service_name') ? Str::studly(config('app.name')) : "";

        switch ($method) {
            case 'index':
                return $serviceName . 'GetAll' . Str::plural($candidate);
            case 'store':
                return $serviceName . 'Create' . $candidate;
            case 'show':
                return $serviceName . 'Get' . $candidate;
            case 'update':
                return $serviceName . ucfirst($operation) . $candidate;
            case 'destroy':
                return $serviceName . 'Delete' . $candidate;
            default:
                return $serviceName . ucfirst($method) . $candidate;
        }
    }
}
