<?php
namespace Kyrenator\QueryCache;

use Cache;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Query\Grammars\Grammar;
use Illuminate\Database\Query\Processors\Processor;

/**
 * @property array|mixed cacheTags
 */
class CachableQueryBuilder extends Builder {

    /**
     * The key that should be used when caching the query.
     *
     * @var string
     */
    protected $cacheKey;

    /**
     * The number of minutes to cache the query.
     *
     * @var int
     */
    protected $cacheMinutes;

    protected $cache;

    /**
     * Create a new query builder instance.
     *
     * @param  \Illuminate\Database\ConnectionInterface $connection
     * @param  \Illuminate\Database\Query\Grammars\Grammar $grammar
     * @param  \Illuminate\Database\Query\Processors\Processor $processor
     */
    public function __construct(ConnectionInterface $connection, Grammar $grammar, Processor $processor)
    {
        parent::__construct($connection, $grammar, $processor);
    }


    /**
     * Create a new cachable query builder instance.
     *
     * @param $minutes
     * @param null $key
     * @return $this
     * @internal param Cache $cache
     */

    public function remember($minutes, $key = null)
    {
        list($this->cacheMinutes, $this->cacheKey) = array($minutes, $key);

        return $this;
    }

    /**
     * Indicate that the query results should be cached forever.
     *
     * @param  string $key
     * @return \Illuminate\Database\Query\Builder|static
     */
    public function rememberForever($key = null)
    {
        return $this->remember(-1, $key);
    }

    /**
     * Execute the query as a cached "select" statement.
     *
     * @param  array $columns
     * @return array
     */
    public function getCached($columns = array('*'))
    {
        if (is_null($this->columns)) $this->columns = $columns;

        // If the query is requested to be cached, we will cache it using a unique key
        // for this database connection and query statement, including the bindings
        // that are used on this query, providing great convenience when caching.
        list($key, $minutes) = $this->getCacheInfo();

        $callback = $this->getCacheCallback($columns);

        // If the "minutes" value is less than zero, we will use that as the indicator
        // that the value should be remembered values should be stored indefinitely
        // and if we have minutes we will use the typical remember function here.
        if ($minutes < 0)
        {
            //check if cache driver supports tags
            if ($this->getCacheTags())
                return Cache::tags($this->getCacheTags())->rememberForever($key, $callback, 60);
            else
                return Cache::rememberForever($key, $callback, 60);
        }
        //check if cache driver supports tags
        if ($this->getCacheTags())
            return Cache::tags($this->getCacheTags())->remember($key, $minutes, $callback);
        else
            return Cache::remember($key, $minutes, $callback);
    }


    /**
     * Get the Closure callback used when caching queries.
     *
     * @param  array $columns
     * @return \Closure
     */
    protected function getCacheCallback($columns)
    {
        return function () use ($columns)
        {
            return $this->getFresh($columns);
        };
    }

    /**
     * Get a unique cache key for the complete query.
     *
     * @return string
     */
    public function getCacheKey()
    {
        return $this->cacheKey ?: $this->generateCacheKey();
    }

    /**
     * Generate the unique cache key for the query.
     *
     * @return string
     */
    public function generateCacheKey()
    {
        $name = $this->connection->getName();

        return md5($name . $this->toSql() . serialize($this->getBindings()));
    }

    /**
     * Get the cache key and cache minutes as an array.
     *
     * @return array
     */
    protected function getCacheInfo()
    {
        return array($this->getCacheKey(), $this->cacheMinutes);
    }


    /**
     * Indicate that the results, if cached, should use the given cache tags.
     *
     * @param  array|mixed $cacheTags
     * @return $this
     */
    public function cacheTags($cacheTags)
    {
        if (is_array($cacheTags))
        {
            foreach ($cacheTags as $tag)
            {
                $this->cacheTags[] = $tag;
            }
            return $this;
        }
        $this->cacheTags[] = $cacheTags;
        return $this;
    }

    /**
     * Get the cache object with tags assigned, if applicable.
     *
     * @return \Illuminate\Cache\CacheManager
     */
    protected function getCacheTags()
    {
        if ((Cache::getDefaultDriver() != 'file') && (Cache::getDefaultDriver() != 'database'))
        {
            return implode(",", $this->cacheTags);
        }
    }

    /**
     * Execute the query as a "select" statement.
     *
     * @param  array $columns
     * @return array|static[]
     */
    public function get($columns = array('*'))
    {
        if (!is_null($this->cacheMinutes)) return $this->getCached($columns);

        return $this->getFresh($columns);
    }

}