<?php

declare(strict_types=1);

namespace Asseco\OpenApi\Exceptions;

use Exception;
use Throwable;

class OpenApiException extends Exception
{
    public function __construct($message = '', $code = 0, Throwable $previous = null)
    {
        parent::__construct('[OpenApiException] ' . $message, $code, $previous);
    }
}
