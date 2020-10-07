<?php

namespace Voice\OpenApi;

use Closure;
use Exception;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Config;
use Voice\OpenApi\Exceptions\OpenApiException;

class RouteWrapper
{
    private Route $route;
    protected string $controllerName;
    protected string $controllerMethod;

    protected array $excludeRules;
    protected bool $verbose;

    public function __construct(Route $route)
    {
        if (!array_key_exists('uses', $route->getAction())) {
            throw new OpenApiException("Route '{$route->getName()}' is missing mandatory data.");
        }

        $this->excludeRules = Config::get('asseco-open-api.exclude');
        $this->verbose = Config::get('asseco-open-api.verbose');

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

    public function controllerName()
    {
        if (isset($this->controllerName)) {
            return $this->controllerName;
        }

        $this->controllerName = $this->explodeAction()[0];

        return $this->controllerName;
    }

    public function controllerMethod()
    {
        if (isset($this->controllerMethod)) {
            return $this->controllerMethod;
        }

        $this->controllerMethod = $this->explodeAction()[1];

        return $this->controllerMethod;
    }

    protected function explodeAction()
    {
        $exploded = explode('@', $this->action()['uses']);

        if (sizeof($exploded) < 2) {
            throw new Exception("Exploding {$this->route->getName()} route controller@action resulted in error.");
        }

        return $exploded;
    }

    public function operations(): array
    {
        return array_map(function ($method) {
            return strtolower($method);
        }, array_diff($this->route->methods(), ['HEAD']));
    }

    public function getPathParameters(): array
    {
        if (!$this->hasPathParameters()) {
            return [];
        }

        preg_match_all('/{(.*?)}/', $this->path(), $matches);

        if (count($matches) < 2) {
            throw new Exception("Regex match failed for {$this->path()}");
        }

        $parameters = [];
        foreach ($matches[1] as $match) {

            $required = preg_match('/\?/', $match);

            $parameters[] = [
                'name'     => str_replace('?', '', $match),
                'required' => $required,
            ];
        }

        return $parameters;
    }

    protected function hasPathParameters(): bool
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

        if ($isClosure && $this->verbose) echo "Skipping {$this->path()}, closure routes not supported.\n";

        return $isClosure;
    }

    public function excludedByName(): bool
    {
        $byName = $this->excludeRules['route_name'];

        foreach ($byName as $name) {
            if ($this->route->getName() && (preg_match('/' . $name . '/', $this->route->getName()))) {
                if ($this->verbose) echo "Excluding route by name: '{$this->route->getName()}'\n";
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
                if ($this->verbose) echo "Excluding route by controller: '{$controllerClass}'\n";
                return true;
            }
        }

        return false;
    }
}
