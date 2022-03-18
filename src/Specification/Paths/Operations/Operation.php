<?php

declare(strict_types=1);

namespace Asseco\OpenApi\Specification\Paths\Operations;

use Asseco\OpenApi\Contracts\Serializable;
use Asseco\OpenApi\Exceptions\OpenApiException;
use Asseco\OpenApi\Specification\Paths\Operations\Parameters\Parameters;

class Operation implements Serializable
{
    protected const OPERATIONS = ['get', 'post', 'put', 'patch', 'delete'];

    protected array $methodData;
    protected string $operation;
    protected array $requestBody = [];
    protected array $responses = [];
    protected array $parameters = [];

    /**
     * Operation constructor.
     *
     * @param  array  $methodData
     * @param  string  $operation
     *
     * @throws OpenApiException
     */
    public function __construct(array $methodData, string $operation)
    {
        if (!in_array($operation, self::OPERATIONS)) {
            throw new OpenApiException("Operation '$operation' unsupported.");
        }

        $this->operation = $operation;
        $this->methodData = $methodData;
    }

    public function appendParameters(?Parameters $parameters): void
    {
        if (!$parameters) {
            return;
        }

        $this->parameters = $parameters->toSchema();
    }

    public function appendResponses(Responses $responses): void
    {
        $this->responses = $responses->toSchema();
    }

    public function appendRequestBody(?RequestBody $requestBody): void
    {
        if (!$requestBody) {
            return;
        }

        $this->requestBody = $requestBody->toSchema();
    }

    public function toSchema(): array
    {
        $schema = array_merge_recursive(
            $this->methodData,
            $this->parameters,
            $this->requestBody,
            $this->responses,
        );

        return [$this->operation => $schema];
    }
}
