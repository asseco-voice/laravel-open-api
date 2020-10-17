<?php

namespace Voice\OpenApi;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Mpociot\Reflection\DocBlock;
use ReflectionClass;
use ReflectionException;
use Voice\OpenApi\Extractors\ExceptExtractor;
use Voice\OpenApi\Extractors\GroupExtractor;
use Voice\OpenApi\Extractors\MethodExtractor;
use Voice\OpenApi\Extractors\ModelExtractor;
use Voice\OpenApi\Extractors\MultipleExtractor;
use Voice\OpenApi\Extractors\PathParameterExtractor;
use Voice\OpenApi\Extractors\RequestBodyExtractor;
use Voice\OpenApi\Extractors\ResponseBodyExtractor;

class ReflectionExtractor
{
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
        return (new ModelExtractor())($this->controllerDocBlock, $this->controller, $namespace, $candidate);
    }

    /**
     * @return array
     * @throws Exceptions\OpenApiException
     */
    public function getRequest()
    {
        return (new RequestBodyExtractor())($this->methodDocBlock);
    }

    /**
     * @return array
     * @throws Exceptions\OpenApiException
     */
    public function getResponse()
    {
        return (new ResponseBodyExtractor())($this->methodDocBlock);
    }

    public function getExceptAttributes()
    {
        return (new ExceptExtractor())($this->methodDocBlock);
    }

    public function getPathParameters(array $routeParameters)
    {
        return (new PathParameterExtractor())($this->methodDocBlock, $routeParameters);
    }

    public function getGroup(string $candidate)
    {
        return (new GroupExtractor())($this->methodDocBlock, $this->controllerDocBlock, $candidate);
    }

    public function getMethodData(string $candidate)
    {
        $groups = $this->getGroup($candidate);

        return (new MethodExtractor())($this->methodDocBlock, $groups);
    }

    public function getMultiple()
    {
        return (new MultipleExtractor())($this->methodDocBlock);
    }
}
