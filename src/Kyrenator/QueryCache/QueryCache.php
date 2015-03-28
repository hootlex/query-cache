<?php
namespace Kyrenator\QueryCache;
use Kyrenator\QueryCache\CachableQueryBuilder as QueryBuilder;
trait QueryCache {

    protected $cacheTagsPassed;
    /**
     * Boot the Active Events trait for a model.
     *
     * @return void
     */
    public static function bootQueryCache()
    {
        $model = parent::getModel();
        $cacheTagsPassed = self::getCacheTags($model);

        if($model->cacheAll)
        {
            static::addGlobalScope(new QueryCacheScope($cacheTagsPassed));
        }
        if($model->clearOnChange)
        {
            static::creating(function ()
            {
                parent::clearCache();
            });
            static::updating(function ()
            {
                parent::clearCache();
            });
            static::deleting(function ()
            {
                parent::clearCache();
            });
        }
    }

    /**
     * Get a new query builder without cache.
     *
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public static function uncached()
    {
        return (new static)->newQueryWithoutScope(new QueryCacheScope(null));
    }

    public static function clearCache()
    {
        $tag = parent::getModel()->getTable();
        \Cache::tags($tag)->flush();

    }

    /**
     * @param $model
     * @return mixed
     */
    public static function getCacheTags($model)
    {
        if ($model->cacheTags)
        {
            $cacheTags = $model->cacheTags;
            return $cacheTags;
        } else
        {
            $cacheTags = $model->getTable();
            return $cacheTags;
        }
    }



    /**
     * Get a new cachable query builder instance for the connection.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    protected function newBaseQueryBuilder()
    {
        $conn = $this->getConnection();
        $grammar = $conn->getQueryGrammar();
        return new QueryBuilder($conn, $grammar, $conn->getPostProcessor());
    }

}