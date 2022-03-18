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
    protected const TTL = 60 * 60 * 24;

    public static function get(Model $model): array
    {
        $cacheKey = self::CACHE_PREFIX_DB . get_class($model);

        if (Cache::has($cacheKey) && !config('asseco-open-api.bust_cache')) {
            return Cache::get($cacheKey);
        }

        $table = $model->getTable();

        $columns = self::getColumnAttributes($table);
        Cache::put($cacheKey, $columns, self::TTL);

        return $columns;
    }

    public static function getPivot(string $table): array
    {
        $cacheKey = self::CACHE_PREFIX_DB . $table;

        if (Cache::has($cacheKey) && !config('asseco-open-api.bust_cache')) {
            return Cache::get($cacheKey);
        }

        $columns = self::getColumnAttributes($table);
        Cache::put($cacheKey, $columns, self::TTL);

        return $columns;
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
                $required = DB::connection()->getDoctrineColumn($table, $column)->getNotnull();
                $type = self::remapColumnTypes(Schema::getColumnType($table, $column));
                $modelColumns[] = new Column($column, $type, $required);
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
            case 'time':
            case 'datetime':
            case 'date':
            case 'text':
            case 'guid':
                return 'string';
            case 'float':
                return 'number';
            default:
                return $columnType;
        }
    }
}
