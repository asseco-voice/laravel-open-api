<?php

namespace Voice\OpenApi\Specification\Parts;

use Voice\OpenApi\Contracts\Serializable;
use Voice\OpenApi\Extractor;
use Voice\OpenApi\Specification\Parts\DataTypes\Integer;
use Voice\OpenApi\Specification\Parts\Parameters\Parameter;
use Voice\OpenApi\Specification\Parts\Parameters\PathParameter;

class Parameters implements Serializable
{
    private Extractor $extractor;
    protected array $parameters = [];

    public function __construct(Extractor $extractor)
    {
        $this->extractor = $extractor;
    }

    public function append(Parameter $parameter)
    {
        $this->parameters[] = $parameter->toSchema();
    }

    public function toSchema(): array
    {
        return ['parameters' => $this->parameters];
    }

    public function generateParameters(array $parameters)
    {
        if (empty($parameters)) {
            return;
        }

//        // TODO: guess with multi parameters
//        if ($this->extractor->model) {
//            $keyName = (new $this->extractor->model)->getRouteKeyName();
//            $type = $this->extractor->getTypeForColumn($keyName);
//        }

        foreach ($parameters as $parameter) {

            // TODO: remove hard coding
            $dataType = new Integer();
            $parameterType = new PathParameter($parameter['name'], $dataType);

            $this->append($parameterType);
        }
    }
}
