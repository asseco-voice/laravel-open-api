<?php

namespace Voice\OpenApi\Traits;

use Voice\OpenApi\Exceptions\OpenApiException;

trait ParsesStringToBoolean
{
    /**
     * @param string $required
     * @return bool
     * @throws OpenApiException
     */
    protected function parseBooleanString(string $required): bool
    {
        if ($required === 'true' || $required === '1') {
            return true;
        } elseif ($required === 'false' || $required === '0') {
            return false;
        } else {
            throw new OpenApiException("Required property must be boolean.");
        }
    }
}
