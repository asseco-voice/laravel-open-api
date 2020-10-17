<?php

namespace Voice\OpenApi\Specification\Paths\Operations;

use Voice\OpenApi\Contracts\Serializable;
use Voice\OpenApi\Exceptions\OpenApiException;
use Voice\OpenApi\Specification\Paths\Operations\Parameters\Parameters;

class Operation implements Serializable
{
    protected const OPERATIONS = ['get', 'post', 'put', 'patch', 'delete'];

    protected string $operation;
    protected array $responses = [];
    protected array $options = [];
    protected array $parameters = [];
    protected array $requestBody = [];
    private array $methodData;

    /**
     * Operation constructor.
     * @param array $methodData
     * @param string $operation
     * @param array $options
     * @throws OpenApiException
     */
    public function __construct(array $methodData, string $operation, array $options = [])
    {
        if (!in_array($operation, self::OPERATIONS)) {
            throw new OpenApiException("Operation '$operation' unsupported.");
        }

        $this->operation = $operation;
        $this->methodData = $methodData;
        $this->options = $this->generateOptions($options);
    }

    protected function generateOptions(array $options): array
    {
        return array_merge($this->methodData, $options);
    }

    public function appendParameters(Parameters $parameters): void
    {
        $this->parameters = $parameters->toSchema();
    }

    public function generateResponses(bool $multiple): void
    {
        $responses = new Responses();

        $responses->generateResponse($multiple, '200', 'Successful request.');

        $this->appendResponses($responses);
    }

    public function appendResponses(Responses $responses): void
    {
        $this->responses = $responses->toSchema();
    }

    public function appendRequestBody(RequestBody $requestBody): void
    {
        $this->requestBody = $requestBody->toSchema();
    }

    public function toSchema(): array
    {
        $schema = array_merge_recursive(
            $this->options,
            $this->parameters,
            $this->requestBody,
            $this->responses,
        );

        return [$this->operation => $schema];
    }
}
