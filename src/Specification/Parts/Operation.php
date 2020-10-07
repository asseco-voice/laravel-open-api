<?php


namespace Voice\OpenApi\Specification\Parts;


use Voice\OpenApi\Contracts\Serializable;
use Voice\OpenApi\Exceptions\OpenApiException;
use Voice\OpenApi\Specification\Parameters;
use Voice\OpenApi\Specification\Responses;

class Operation implements Serializable
{
    protected const METHODS = ['get', 'post', 'put', 'patch', 'delete'];

    protected string $method;
    protected array $responses;
    protected array $options;
    protected array $parameters = [];

    /**
     * Operation constructor.
     * @param string $method
     * @param Responses $responses
     * @param array $options
     * @throws OpenApiException
     */
    public function __construct(string $method, Responses $responses, array $options = [])
    {
        if (!in_array($method, self::METHODS)) {
            throw new OpenApiException("Method '$method' unsupported.");
        }

        $this->method = $method;
        $this->responses = $responses->toSchema();
        $this->options = $options;
    }

    public function appendParameters(Parameters $parameters)
    {
        $this->parameters = $parameters->toSchema();
    }

    public function toSchema(): array
    {
        $schema = array_merge_recursive(
            $this->options,
            $this->parameters,
            $this->responses
        );

        return [$this->method => $schema];
    }
}
