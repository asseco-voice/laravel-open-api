<?php

namespace Voice\OpenApi;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class Extractor
{
    public const CACHE_PREFIX = 'open_api_db_schema_';

    protected string $controller;
    protected string $candidate;
    public string    $groupTag;
    public ?string   $model;

    public function __construct(string $controller)
    {
        $this->controller = $controller;

        $this->candidate = $this->getModelCandidate();
        $this->groupTag = $this->getGroupTag();
        $this->model = $this->guessModel(); // ili candidate ako nema modela?
    }

    /**
     * Parse possible model name from controller. At this point we still don't know
     * if this class exists or not.
     *
     * @return string
     */
    protected function getModelCandidate(): string
    {
        $split = explode('\\', $this->controller);
        $controllerName = end($split);

        return str_replace('Controller', '', $controllerName);
    }

    protected function getGroupTag(): string
    {
        // Split words by uppercase letter.
        $split = preg_split('/(?=[A-Z])/', $this->candidate);
        // Unsetting first element because it is always empty.
        unset($split[0]);

        return Str::plural(implode(' ', $split));
    }

    protected function guessModel(): ?string
    {
        $namespaces = Config::get('asseco-open-api.namespaces');

        foreach ($namespaces as $namespace) {

            $classCandidate = $namespace . $this->candidate;

            if (class_exists($classCandidate)) {
                return $classCandidate;
            }
        }

        return null;
    }

    public function fullModelName(): string
    {
        return str_replace(['\\', ' '], '', $this->model);
    }

    public function requestModelName()
    {
        return 'Request__' . $this->fullModelName();
    }

    public function responseModelName()
    {
        return 'Response__' . $this->fullModelName();
    }

    public function modelColumns(): array
    {
        if (!$this->model) {
            return [];
        }

        $cacheKey = self::CACHE_PREFIX . $this->model;

        if (Cache::has($cacheKey) && !Config::get('asseco-open-api.bust_cache')) {
            return Cache::get($cacheKey);
        }

        /**
         * @var $model Model
         */
        $model = new $this->model;

        $table = $model->getTable();
        $columns = Schema::getColumnListing($table);
        $modelColumns = [];

        try { // having 'enum' in table definition will throw Doctrine error because it is not defined in their types.
            foreach ($columns as $column) {
                $modelColumns[$column] = $this->remapColumnTypes(Schema::getColumnType($table, $column));
            }
        } catch (\Exception $e) {
            // what then...?
        }

        Cache::put($cacheKey, $modelColumns, 60 * 60 * 24);

        return $modelColumns;
    }

    protected function remapColumnTypes($columnType)
    {
        switch ($columnType) {
            case 'bigint':
                return 'integer';
            case 'datetime':
            case 'date':
            case 'text':
                return 'string';
            case 'float':
                return 'number';
            default:
                return $columnType;
        }
    }

    public function getTypeForColumn(string $column)
    {
        $modelColumns = $this->modelColumns();

        return array_key_exists($column, $modelColumns) ? $modelColumns[$column] : 'string';
    }
}
