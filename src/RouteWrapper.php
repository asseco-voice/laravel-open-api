<?php

namespace Voice\OpenApi;

use Closure;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Config;
use Voice\OpenApi\Exceptions\OpenApiException;
use Voice\OpenApi\Specification\Paths\Operations\Parameters\DataTypes\IntegerType;
use Voice\OpenApi\Specification\Paths\Operations\Parameters\PathParameter;

class RouteWrapper
{
    private Route $route;
    protected string $controllerName;
    protected string $controllerMethod;

    protected array $excludeRules;
    protected bool $verbose;

    /**
     * RouteWrapper constructor.
     * @param Route $route
     * @throws OpenApiException
     */
    public function __construct(Route $route)
    {
        if (!array_key_exists('uses', $route->getAction())) {
            throw new OpenApiException("Route '{$route->getName()}' is missing mandatory data.");
        }

        $this->excludeRules = config('asseco-open-api.exclude');
        $this->verbose = config('asseco-open-api.verbose');

        $this->route = $route;
    }

    public function path(): string
    {
        // Removing '?' because OpenAPI standard doesn't support optional path parameters.
        return str_replace('?', '', "/{$this->route->uri()}");
    }

    public function action(): array
    {
        return $this->route->getAction();
    }

    /**
     * @return string
     * @throws OpenApiException
     */
    public function controllerName(): string
    {
        if (isset($this->controllerName)) {
            return $this->controllerName;
        }

        $this->controllerName = $this->explodeAction()[0];

        return $this->controllerName;
    }

    /**
     * @return string
     * @throws OpenApiException
     */
    public function controllerMethod(): string
    {
        if (isset($this->controllerMethod)) {
            return $this->controllerMethod;
        }

        $this->controllerMethod = $this->explodeAction()[1];

        return $this->controllerMethod;
    }

    /**
     * @return array
     * @throws OpenApiException
     */
    protected function explodeAction(): array
    {
        $exploded = explode('@', $this->action()['uses']);

        if (sizeof($exploded) < 2) {
            throw new OpenApiException("Exploding {$this->route->getName()} route controller@action resulted in error.");
        }

        return $exploded;
    }

    public function operations(): array
    {
        return array_map(function ($method) {
            return strtolower($method);
        }, array_diff($this->route->methods(), ['HEAD']));
    }

    /**
     * @return array
     * @throws OpenApiException
     */
    public function getPathParameters(): array
    {
        if (!$this->hasPathParameters()) {
            return [];
        }

        preg_match_all('/{(.*?)}/', $this->path(), $matches);

        if (count($matches) < 2) {
            throw new OpenApiException("Regex match failed for {$this->path()}");
        }

        $parameters = [];
        foreach ($matches[1] as $match) {
            $name = str_replace('?', '', $match);
            $type = new IntegerType();
            $description = 'Path parameter';

            $parameter = new PathParameter($name, $type);
            $parameter->addDescription($description);

            $parameters[] = $parameter;
        }

        return $parameters;
    }

    public function hasPathParameters(): bool
    {
        return preg_match('/{.*}/', $this->path());
    }

    public function shouldSkip(): bool
    {
        return $this->isClosure() || $this->excludedByName() || $this->excludedByController();
    }

    public function isClosure(): bool
    {
        $isClosure = $this->action()['uses'] instanceof Closure;

        if ($isClosure && $this->verbose) {
            echo "Skipping {$this->path()}, closure routes not supported.\n";
        }

        return $isClosure;
    }

    public function excludedByName(): bool
    {
        $byName = $this->excludeRules['route_name'];

        foreach ($byName as $name) {
            if ($this->route->getName() && (preg_match('/' . $name . '/', $this->route->getName()))) {
                if ($this->verbose) {
                    echo "Excluding route by name: '{$this->route->getName()}'\n";
                }

                return true;
            }
        }

        return false;
    }

    public function excludedByController(): bool
    {
        $byController = $this->excludeRules['controller_name'];

        foreach ($byController as $controller) {
            $controllerClass = get_class($this->route->getController());

            if ($controller === $controllerClass) {
                if ($this->verbose) {
                    echo "Excluding route by controller: '{$controllerClass}'\n";
                }

                return true;
            }
        }

        return false;
    }
}
