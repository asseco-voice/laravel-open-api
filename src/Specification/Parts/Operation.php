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
    protected array $responses;
    protected array $options;
    protected array $parameters = [];

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

    public function generateResponses()
    {
        $responses = new Responses();

        $responses->generateResponse($this->extractor->oneWordNamespacedModel());

        $this->appendResponses($responses);
    }

    public function generateParameters($pathParameters)
    {
        $parameters = new Parameters($this->extractor);

        $parameters->generateParameters($pathParameters);

        $this->appendParameters($parameters);
    }

    public function appendResponses(Responses $responses)
    {
        $this->responses = $responses->toSchema();
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

        return [$this->operation => $schema];
    }
}
