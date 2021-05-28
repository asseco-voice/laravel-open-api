<?php

namespace Asseco\OpenApi\Traits;

use Asseco\OpenApi\Exceptions\OpenApiException;

trait ParsesStringToBoolean
{
    /**
     * @param string $required
     * @return bool
     * @throws OpenApiException
     */
    protected static function parseBooleanString(string $required): bool
    {
        if ($required === 'true' || $required === '1') {
            return true;
        } elseif ($required === 'false' || $required === '0') {
            return false;
        } else {
            throw new OpenApiException("Required property must be boolean, '$required' provided");
        }
    }
}
