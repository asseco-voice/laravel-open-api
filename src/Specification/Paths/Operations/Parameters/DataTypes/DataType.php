<?php

namespace Voice\OpenApi\Specification\Paths\Operations\Parameters\DataTypes;

use Illuminate\Support\Facades\Config;
use Voice\OpenApi\Contracts\Serializable;
use Voice\OpenApi\Exceptions\OpenApiException;

abstract class DataType implements Serializable
{
    protected array $options;

    public function __construct(array $options = [])
    {
        $this->options = $options;
    }

    public static function getMappedClass(string $type): self
    {
        $supported = Config::get('asseco-open-api.data_types');

        if(!array_key_exists($type, $supported)){
            throw new OpenApiException("Type '$type' is not supported.");
        }

        return new $supported[$type];
    }
}
