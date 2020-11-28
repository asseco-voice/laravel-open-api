<?php

declare(strict_types=1);

namespace Asseco\OpenApi\Specification\Paths\Operations\Parameters\DataTypes;

use Asseco\OpenApi\Contracts\Serializable;
use Asseco\OpenApi\Exceptions\OpenApiException;
use Illuminate\Support\Facades\Config;

abstract class DataType implements Serializable
{
    protected array $options;

    public function __construct(array $options = [])
    {
        $this->options = $options;
    }

    /**
     * @param string $type
     * @return static
     * @throws OpenApiException
     */
    public static function getMappedClass(string $type): self
    {
        $supported = config('asseco-open-api.data_types');

        if (!array_key_exists($type, $supported)) {
            throw new OpenApiException("Type '$type' is not supported.");
        }

        return new $supported[$type];
    }
}
