# Laravel 5 query cache

This Laravel 5 package allows you to easily cache eloquent queries by implementing laravel 4 remember method.

##How to use
###Step 1: Install Through Composer
```
composer require kyrenator/query-cache
```
###Step 2: Use QueryCache In Your Model
```php
<?php namespace App;

use Kyrenator\QueryCache\QueryCache;
use Illuminate\Database\Eloquent\Model;

class Post extends Model {
    use QueryCache;
}
```
###Step 3: Use remember Method When Quering Eloquent
When calling remember method you can tell it for how many minutes you want the query be cached.
If you dont specify the minutes, the query will be cached for 60 minutes.
```php
\App\Post::remember()->take(3)->get();

//use cache tags
\App\Post::remember()->cacheTags('posts', 'fresh')->take(3)->get();
```

##More Features
###Global Cache
If you want you can cache all queries for a specific model by siply defining cacheAll var inside your model.
QueryCache will aply remember method to all model queries.
```php
<?php namespace App;

use Kyrenator\QueryCache\QueryCache;
use Illuminate\Database\Eloquent\Model;

class Post extends Model {
    use QueryCache;
    protected $cacheAll = true;
}
```
###Clear Cache On Change
If you want the cache to be flushed when you create, delete, or update an existing model then define $clearOnChange
```php
<?php namespace App;

use Kyrenator\QueryCache\QueryCache;
use Illuminate\Database\Eloquent\Model;

class Post extends Model {
    use QueryCache;
    protected $clearOnChange = true;
}
``` 
###Cache Tags
QueryCache will use the model name as cache tags. You can also define custon cache tags.
```php
<?php namespace App;

use Kyrenator\QueryCache\QueryCache;
use Illuminate\Database\Eloquent\Model;

class Post extends Model {
    use QueryCache;
    protected $clearOnChange = true;
    protected $cacheTags = 'fresh';
    protected $cacheAll = true;
}
``` 

