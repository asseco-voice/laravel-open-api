<?php

namespace Voice\OpenApi;

use Illuminate\Database\Eloquent\Model;

class NameExtractor
{
    protected const DEFAULT_NAMESPACE = 'App';
    private string $controller;
    private string $method;

    public function __construct(string $controller, string $method)
    {
        $this->controller = $controller;
        $this->method = $method;
        $this->namespace = $this->guessNamespace();
        $this->candidate = $this->getModelCandidate();
    }

    /**
     * Try to guess model namespace from controller, assuming App part of the
     * namespace exist.
     *
     * I.e.
     *
     * My\Namespace\App\Http\Controllers\MyController will return My\Namespace\App\
     *
     * My\Non\Laravel\Namespace\MyController will return App\
     *
     * @param string $controller
     * @return string
     */
    protected function guessNamespace(): string
    {
        $split = preg_split('|' . self::DEFAULT_NAMESPACE . '|', $this->controller);

        if (count($split) < 2) {
            return self::DEFAULT_NAMESPACE . '\\';
        }

        return $split[0] . self::DEFAULT_NAMESPACE . '\\';
    }

    /**
     * Parse possible model name from controller.
     * At this point we still don't know if this class exists or not.
     *
     * @param string $controller
     * @return string
     */
    protected function getModelCandidate(): string
    {
        $split = explode('\\', $this->controller);
        $controllerName = end($split);

        return str_replace('Controller', '', $controllerName);
    }

    public function concatModelName(?Model $model): string
    {
        $controller = str_replace([$this->namespace, 'Http\\Controllers\\', '\\', ' '], '', $this->controller);
        $namespace = str_replace(['\\', ' '], '', $this->namespace);

        $prefix = "{$this->method}_{$namespace}_{$controller}_";

        if (!$model) {
            return $prefix . $this->candidate;
        }

        $modelName = str_replace(['\\', ' '], '', get_class($model));

        return $prefix . str_replace($namespace, '', $modelName);
    }

    public function prependModelName(string $prefix, ?Model $model)
    {
        return "{$prefix}_{$this->concatModelName($model)}";
    }
}
