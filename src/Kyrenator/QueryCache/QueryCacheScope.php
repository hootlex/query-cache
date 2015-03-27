<?php namespace Kyrenator\QueryCache;

use Illuminate\Database\Eloquent\Builder as Builder;
use Illuminate\Database\Eloquent\Model as Model;
use Illuminate\Database\Eloquent\ScopeInterface;

class QueryCacheScope implements ScopeInterface{

    protected $cacheMinutes;
    protected $cacheTags;


    function __construct($cacheTags, $cacheMinutes = 60)
    {
        $this->cacheTags = $cacheTags;
        $this->cacheMinutes = $cacheMinutes;

    }


    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
            $builder->remember($this->cacheMinutes)->cacheTags($this->cacheTags);
    }

    /**
     * Remove the scope from the given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $builder
     * @param  \Illuminate\Database\Eloquent\Model $model
     *
     * @return void
     */
    public function remove(Builder $builder, Model $model)
    {
        $builder->remember(null);
    }

}
