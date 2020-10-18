<?php

namespace Voice\OpenApi;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Mpociot\Reflection\DocBlock;
use Mpociot\Reflection\DocBlock\Tag;
use ReflectionClass;
use ReflectionException;
use Voice\OpenApi\Guessers\GroupGuesser;
use Voice\OpenApi\Parsers\ModelHandler;
use Voice\OpenApi\Parsers\PathHandler;
use Voice\OpenApi\Parsers\RequestResponseHandler;
use Voice\OpenApi\Specification\Paths\Operations\Parameters\Parameters;
use Voice\OpenApi\Traits\ParsesStringToBoolean;

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
        $modelTag = $this->getTags($this->controllerDocBlock, self::MODEL);

        return (new ModelHandler($modelTag))->parse($this->controller, $namespace, $candidate);
    }

    /**
     * @return array
     * @throws Exceptions\OpenApiException
     */
    public function getRequest()
    {
        $tags = $this->getTags($this->methodDocBlock, self::REQUEST);

        return (new RequestResponseHandler($tags))->parse();
    }

    /**
     * @return array
     * @throws Exceptions\OpenApiException
     */
    public function getResponse()
    {
        $tags = $this->getTags($this->methodDocBlock, self::RESPONSE);

        return (new RequestResponseHandler($tags))->parse();
    }

    public function getExceptAttributes()
    {
        $responseTags = $this->getTags($this->methodDocBlock, self::EXCEPT);

        return $responseTags ? explode(' ', $responseTags[0]) : [];
    }

    /**
     * @param array $routeParameters
     * @return Parameters|null
     * @throws Exceptions\OpenApiException
     */
    public function getPathParameters(array $routeParameters): ?Parameters
    {
        $pathTags = $this->getTags($this->methodDocBlock, self::PATH);

        return (new PathHandler($pathTags))->parse($routeParameters);
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
        $multipleTag = $this->getTags($this->methodDocBlock, self::MULTIPLE);

        if(!$multipleTag){
            return false;
        }

        return $this->parseBooleanString($multipleTag[0]);
    }

    protected function getTags(DocBlock $docBlock, string $tagName): array
    {
        $methodGroups = $docBlock->getTagsByName($tagName);

        return $this->getTagContent($methodGroups);
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
