<?php

namespace Asseco\OpenApi\Handlers;

use Asseco\OpenApi\Exceptions\OpenApiException;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class OperationIdHandler extends AbstractHandler
{
    /**
     * @param  array  $tags
     * @param  string  $candidate
     * @param  string  $method
     * @param  string  $operation
     * @return string
     *
     * @throws OpenApiException
     */
    public static function handle(array $tags, string $candidate, string $method, string $operation): string
    {
        $serviceName = config('asseco-open-api.prepend_service_name') ? Str::studly(config('app.name')) : '';

        if (!$tags) {
            return $serviceName . self::generateOperationId($method, $operation, $candidate);
        }

        $tag = $tags[0];
        $split = explode(' ', $tag);

        self::verifyParameters(count($split));

        return $serviceName . self::generateOperationId($method, $operation, Arr::get($split, '0', $candidate));
    }

    /**
     * @param  int  $count
     *
     * @throws OpenApiException
     */
    protected static function verifyParameters(int $count)
    {
        if ($count > 1) {
            throw new OpenApiException('Wrong number of parameters provided');
        }
    }

    /**
     * @param  string  $method
     * @param  string  $operation
     * @param  string  $candidate
     * @return string
     */
    protected static function generateOperationId(string $method, string $operation, string $candidate): string
    {
        switch ($method) {
            case 'index':
                return 'GetAll' . Str::plural($candidate);
            case 'store':
                return 'Create' . $candidate;
            case 'show':
                return 'Get' . $candidate;
            case 'update':
                return ucfirst($operation) . $candidate;
            case 'destroy':
                return 'Delete' . $candidate;
            default:
                return ucfirst($method) . $candidate;
        }
    }
}
