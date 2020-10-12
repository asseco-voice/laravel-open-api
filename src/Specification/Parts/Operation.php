<?php

namespace Voice\OpenApi\Specification\Parts;

use Mpociot\Reflection\DocBlock;
use Voice\OpenApi\Contracts\Serializable;
use Voice\OpenApi\Exceptions\OpenApiException;
use Voice\OpenApi\Extractor;

class Operation implements Serializable
{
    protected const OPERATIONS = ['get', 'post', 'put', 'patch', 'delete'];

    private Extractor $extractor;
    private DocBlock $methodDocBlock;

    protected string $operation;
    protected array $responses = [];
    protected array $options = [];
    protected array $parameters = [];
    protected array $requestBody = [];

    /**
     * Operation constructor.
     * @param Extractor $extractor
     * @param DocBlock $methodDocBlock
     * @param string $operation
     * @param array $options
     * @throws OpenApiException
     */
    public function __construct(Extractor $extractor, DocBlock $methodDocBlock, string $operation, array $options = [])
    {
        if (!in_array($operation, self::OPERATIONS)) {
            throw new OpenApiException("Operation '$operation' unsupported.");
        }

        $this->extractor = $extractor;
        $this->methodDocBlock = $methodDocBlock;
        $this->operation = $operation;
        $this->options = $this->generateOptions($methodDocBlock, $options);
    }

    protected function generateOptions(DocBlock $methodDocBlock, array $options): array
    {
        return array_merge([
            'summary'     => $methodDocBlock->getShortDescription(),
            'description' => $methodDocBlock->getLongDescription()->getContents(),
            'tags'        => [
                $this->extractor->groupTag
            ],
        ], $options);
    }

    public function generateRequestBody(): void
    {
        if (!$this->extractor->model) {
            return;
        }

        $requestBody = new RequestBody();

        $requestBody->generateContent($this->extractor->requestModelName());

        $this->appendRequestBody($requestBody);
    }

    public function generateResponses(bool $multiple): void
    {
        $responses = new Responses();

        $responses->generateResponse($this->extractor->model, $this->extractor->responseModelName(), $multiple);

        $this->appendResponses($responses);
    }

    public function generateParameters(array $pathParameters): void
    {
        if (!$pathParameters) {
            return;
        }

        $parameters = new Parameters($this->extractor);

        $parameters->generateParameters($pathParameters);

        $this->appendParameters($parameters);
    }

    public function appendResponses(Responses $responses): void
    {
        $this->responses = $responses->toSchema();
    }

    public function appendParameters(Parameters $parameters): void
    {
        $this->parameters = $parameters->toSchema();
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
