<?php

namespace Asseco\OpenApi;

use Asseco\OpenApi\Specification\Shared\Column;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ModelColumns
{
    protected const CACHE_PREFIX_DB = 'open_api_db_schema_';
    private Model $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function modelColumns(): array
    {
        $cacheKey = self::CACHE_PREFIX_DB . get_class($this->model);

        if (Cache::has($cacheKey) && !config('asseco-open-api.bust_cache')) {
            return Cache::get($cacheKey);
        }

        $table = $this->model->getTable();
        $columns = Schema::getColumnListing($table);
        $modelColumns = [];

        // having 'enum' in table definition will throw Doctrine error because it is not defined in their types.
        // Registering it manually.
        DB::connection()->getDoctrineSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
        try {
            foreach ($columns as $column) {
                $type = $this->remapColumnTypes(Schema::getColumnType($table, $column));
                $modelColumns[] = new Column($column, $type, true);
            }
        } catch (Exception $e) {
            echo print_r($e->getMessage(), true) . "\n";
        }

        Cache::put($cacheKey, $modelColumns, 60 * 60 * 24);

        return $modelColumns;
    }

    protected function remapColumnTypes($columnType)
    {
        switch ($columnType) {
            case 'int':
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
}
