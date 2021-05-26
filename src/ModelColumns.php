<?php

namespace Asseco\OpenApi;

use Asseco\OpenApi\Specification\Shared\Column;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ModelColumns
{
    protected const CACHE_PREFIX_DB = 'open_api_db_schema_';

    public static function modelColumns(Model $model): array
    {
        $cacheKey = self::CACHE_PREFIX_DB . get_class($model);

        if (Cache::has($cacheKey) && !config('asseco-open-api.bust_cache')) {
            return Cache::get($cacheKey);
        }

        $table = $model->getTable();

        $modelColumns = self::getColumnAttributes($table);
        Cache::put($cacheKey, $modelColumns, 60 * 60 * 24);

        return $modelColumns;
    }

    public static function pivotColumns(string $table): array
    {
        $cacheKey = self::CACHE_PREFIX_DB . $table;

        if (Cache::has($cacheKey) && !config('asseco-open-api.bust_cache')) {
            return Cache::get($cacheKey);
        }

        $modelColumns = self::getColumnAttributes($table);
        Cache::put($cacheKey, $modelColumns, 60 * 60 * 24);

        return $modelColumns;
    }

    protected static function getColumnAttributes($table): array
    {
        $columns = Schema::getColumnListing($table);
        $modelColumns = [];

        // having 'enum' in table definition will throw Doctrine error because it is not defined in their types.
        // Registering it manually.
        DB::connection()->getDoctrineSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
        try {
            foreach ($columns as $column) {
                $type = self::remapColumnTypes(Schema::getColumnType($table, $column));
                $modelColumns[] = new Column($column, $type, true);
            }
        } catch (Exception $e) {
            echo print_r($e->getMessage(), true) . "\n";
        }

        return $modelColumns;
    }

    protected static function remapColumnTypes($columnType)
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
