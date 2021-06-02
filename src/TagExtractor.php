<?php

namespace Asseco\OpenApi;

use Asseco\OpenApi\Handlers\AppendHandler;
use Asseco\OpenApi\Handlers\ModelHandler;
use Asseco\OpenApi\Handlers\PathHandler;
use Asseco\OpenApi\Handlers\RequestResponseHandler;
use Asseco\OpenApi\Specification\Paths\Operations\Parameters\Parameters;
use Asseco\OpenApi\Tags\AppendTag;
use Asseco\OpenApi\Tags\ExceptTag;
use Asseco\OpenApi\Tags\GroupTag;
use Asseco\OpenApi\Tags\ModelTag;
use Asseco\OpenApi\Tags\MultipleTag;
use Asseco\OpenApi\Tags\PathTag;
use Asseco\OpenApi\Tags\PivotTag;
use Asseco\OpenApi\Tags\RequestTag;
use Asseco\OpenApi\Tags\ResponseTag;
use Asseco\OpenApi\Traits\ParsesStringToBoolean;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Mpociot\Reflection\DocBlock;
use ReflectionClass;
use ReflectionException;

class TagExtractor
{
    use ParsesStringToBoolean;

    protected const CACHE_PREFIX_CONTROLLER = 'open_api_controller_';

    protected string $controller;
    protected string $method;

    protected DocBlock $controllerDocBlock;
    protected DocBlock $methodDocBlock;

    /**
     * ReflectionExtractor constructor.
     * @param string $controller
     * @param string $method
     * @throws ReflectionException
     */
    public function __construct(string $controller, string $method)
    {
        $this->controller = $controller;
        $this->method = $method;

        $reflection = new ReflectionClass($this->controller);
        $this->controllerDocBlock = $this->getControllerDocBlock($reflection);
        $this->methodDocBlock = $this->getMethodDocBlock($reflection);
    }

    protected function getControllerDocBlock(ReflectionClass $reflection): DocBlock
    {
        $key = self::CACHE_PREFIX_CONTROLLER . $this->controller;

        if (Cache::has($key)) {
            return Cache::get($key);
        }

        $docBlock = new DocBlock($reflection->getDocComment());

        Cache::put($key, $docBlock, 10);

        return $docBlock;
    }

    protected function getMethodDocBlock(ReflectionClass $reflection): DocBlock
    {
        return new DocBlock($reflection->getMethod($this->method)->getDocComment());
    }

    public function getModel(string $namespace, string $candidate): ?Model
    {
        $tags = ModelTag::getFrom($this->controllerDocBlock);

        return ModelHandler::handle($tags, $this->controller, $namespace, $candidate);
    }

    /**
     * @return array
     * @throws Exceptions\OpenApiException
     */
    public function getRequest()
    {
        $tags = RequestTag::getFrom($this->methodDocBlock);

        return RequestResponseHandler::handle($tags);
    }

    /**
     * @return array
     * @throws Exceptions\OpenApiException
     */
    public function getResponse()
    {
        $tags = ResponseTag::getFrom($this->methodDocBlock);

        return RequestResponseHandler::handle($tags);
    }

    public function getExceptAttributes()
    {
        $tags = ExceptTag::getFrom($this->methodDocBlock);

        return $tags ? explode(' ', $tags[0]) : [];
    }

    public function getAppendAttributes(string $namespace)
    {
        $tags = AppendTag::getFrom($this->methodDocBlock);

        return AppendHandler::handle($tags, $namespace);
    }

    public function getPivotAttributes()
    {
        $tags = PivotTag::getFrom($this->methodDocBlock);

        return $tags ? $tags[0] : null;
    }

    /**
     * @param array $routeParameters
     * @return Parameters|null
     * @throws Exceptions\OpenApiException
     */
    public function getPathParameters(array $routeParameters): ?Parameters
    {
        $tags = PathTag::getFrom($this->methodDocBlock);

        return PathHandler::handle($tags, $routeParameters);
    }

    public function getGroup(string $candidate)
    {
        $methodGroups = GroupTag::getFrom($this->methodDocBlock);
        $controllerGroups = GroupTag::getFrom($this->controllerDocBlock);

        return $methodGroups ?: $controllerGroups ?: [Guesser::groupName($candidate)];
    }

    public function getMethodData(string $candidate)
    {
        $groups = $this->getGroup($candidate);

        return [
            'summary'     => $this->methodDocBlock->getShortDescription(),
            'description' => $this->methodDocBlock->getLongDescription()->getContents(),
            'tags'        => $groups,
        ];
    }

    public function hasMultipleTag()
    {
        $tags = MultipleTag::getFrom($this->methodDocBlock);

        return !empty($tags);
    }

    public function isResponseMultiple()
    {
        $tags = MultipleTag::getFrom($this->methodDocBlock);

        if (!$tags) {
            return false;
        }

        return self::parseBooleanString($tags[0]);
    }
}
