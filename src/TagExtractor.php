<?php

namespace Asseco\OpenApi;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Mpociot\Reflection\DocBlock;
use Mpociot\Reflection\DocBlock\Tag;
use ReflectionClass;
use ReflectionException;
use Asseco\OpenApi\Guessers\GroupGuesser;
use Asseco\OpenApi\Handlers\AppendHandler;
use Asseco\OpenApi\Handlers\ModelHandler;
use Asseco\OpenApi\Handlers\PathHandler;
use Asseco\OpenApi\Handlers\RequestResponseHandler;
use Asseco\OpenApi\Specification\Paths\Operations\Parameters\Parameters;
use Asseco\OpenApi\Traits\ParsesStringToBoolean;

class TagExtractor
{
    use ParsesStringToBoolean;

    protected const CACHE_PREFIX_CONTROLLER = 'open_api_controller_';

    protected const MODEL = 'model';
    protected const REQUEST = 'request';
    protected const RESPONSE = 'response';
    protected const GROUP = 'group';
    protected const PATH = 'path';
    protected const MULTIPLE = 'multiple';
    protected const EXCEPT = 'except';
    protected const APPEND = 'append';

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

    /**
     * @param ReflectionClass $reflection
     * @return DocBlock
     * @throws ReflectionException
     */
    protected function getMethodDocBlock(ReflectionClass $reflection): DocBlock
    {
        return new DocBlock($reflection->getMethod($this->method)->getDocComment());
    }

    public function getModel(string $namespace, string $candidate): ?Model
    {
        $tags = $this->getTags($this->controllerDocBlock, self::MODEL);

        return (new ModelHandler($tags))->handle($this->controller, $namespace, $candidate);
    }

    /**
     * @return array
     * @throws Exceptions\OpenApiException
     */
    public function getRequest()
    {
        $tags = $this->getTags($this->methodDocBlock, self::REQUEST);

        return (new RequestResponseHandler($tags))->handle();
    }

    /**
     * @return array
     * @throws Exceptions\OpenApiException
     */
    public function getResponse()
    {
        $tags = $this->getTags($this->methodDocBlock, self::RESPONSE);

        return (new RequestResponseHandler($tags))->handle();
    }

    public function getExceptAttributes()
    {
        $tags = $this->getTags($this->methodDocBlock, self::EXCEPT);

        return $tags ? explode(' ', $tags[0]) : [];
    }

    public function getAppendAttributes(string $namespace)
    {
        $tags = $this->getTags($this->methodDocBlock, self::APPEND);

        return (new AppendHandler($tags))->handle($namespace);
    }

    /**
     * @param array $routeParameters
     * @return Parameters|null
     * @throws Exceptions\OpenApiException
     */
    public function getPathParameters(array $routeParameters): ?Parameters
    {
        $tags = $this->getTags($this->methodDocBlock, self::PATH);

        return (new PathHandler($tags))->handle($routeParameters);
    }

    public function getGroup(string $candidate)
    {
        $methodGroups = $this->getTags($this->methodDocBlock, self::GROUP);
        $controllerGroups = $this->getTags($this->controllerDocBlock, self::GROUP);

        return $methodGroups ?: $controllerGroups ?: [(new GroupGuesser())($candidate)];
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
        return !empty($this->getTags($this->methodDocBlock, self::MULTIPLE));
    }

    public function isResponseMultiple()
    {
        $tags = $this->getTags($this->methodDocBlock, self::MULTIPLE);

        if (!$tags) {
            return false;
        }

        return $this->parseBooleanString($tags[0]);
    }

    protected function getTags(DocBlock $docBlock, string $tagName): array
    {
        $tags = $docBlock->getTagsByName($tagName);

        return $this->getTagContent($tags);
    }

    protected function getTagContent(array $groups): array
    {
        return array_map(function ($group) {
            /**
             * @var Tag $group
             */
            return $group->getContent();
        }, $groups);
    }
}
