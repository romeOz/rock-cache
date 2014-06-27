PHP library caching
=================

[![Build Status](https://travis-ci.org/romeo7/rock-cache.svg?branch=master)](https://travis-ci.org/romeo7/rock-cache)

Rock cache on [Packagist](https://packagist.org/packages/romeo7/rock-cache)

What storages can be used:

 * Local storage
 * [Memcached](http://pecl.php.net/package/memcached)
 * [Memcache](http://pecl.php.net/package/memcache)
 * [APCu](http://pecl.php.net/package/APCu)
 * [Redis](http://redis.io)
 * [Couchbase](http://www.couchbase.com)

All storage objects have one interface, so you can switch them without changing the working code.

Features
-------------------

 * One interface for all storages - you can change storage without changing your code
 * Tags for keys (approach versioning and grouping)
 * Autolocker - "dog-pile"/"cache miss storm"/"race condition" effects are excluded
 * Serializer for value (json or PHP serializer)
 * Automatic unserialization

Installation
-------------------

Through Composer, obviously:

```json
{
    "require": {
        "romeo7/rock-cache": "dev-master"
    }
}

```

Quick Start
-------------------

###Memcached

```php

use rock\cache\Memcached;
use rock\cache\CacheInterface;

$config = [
    'hashKey' => CacheInterface::HASH_MD5 // Default: HASH_MD5
    'serializer' => CacheInterface::SERIALIZE_JSON // Default: SERIALIZE_PHP - php serializator
];
$memcached = new Memcached($config);

$tags = ['tag_1', 'tag_2'];
$value = ['foo', 'bar'];
$expire = 0; // If use expire "0", then time to live infinitely
$memcached->set('key_1', $value, $expire, $tags);

// automatic unserialization
$memcached->get('key_1');
// result: ['foo', 'bar'];

$memcached->flush(); // Invalidate all items in the cache
```

###Local storage

```php

use League\Flysystem\Adapter\Local;
use rock\cache\filemanager\FileManager;
use rock\cache\CacheFile;

$adapterConfig = [
    'adapter' => new Local(__DIR__.'/path/to/root'),
];
$adapter = new FileManager($adapterConfig);

$config = [
    'adapter' => $adapter,
    'hashKey' => CacheInterface::HASH_MD5
    'serializer' => CacheInterface::SERIALIZE_JSON
];
$cacheFile = new CacheFile($config)

$cacheFile->set('key_1', 'foo');

$memcached->get('key_1');
// result: foo;
```

Requirements
-------------------

You can use each storage separately, requirements are individually for storages.

 * **PHP 5.4+**
 * **For Local sorage:**
 Used library [flysystem](https://github.com/thephpleague/flysystem) which is an filesystem abstraction which allows you to easily swap out a local filesystem for a remote one. Note: contains composser.
 * **For Redis:**
 [Redis](http://redis.io) server should be installed ```apt-get install redis-server```. Also, should be installed [PHP extension](http://pecl.php.net/package/redis) ```apt-get install php5-redis```
 * **For Memcached/Memcache:**
 [Memcache](http://pecl.php.net/package/memcache) ```apt-get install php5-memcache``` or [Memcached](http://pecl.php.net/package/memcached) ```apt-get install php5-memcached```.
 * **For APCu:**
 [APCu](http://pecl.php.net/package/APCu) should be installed ```apt-get install php5-apcu```.
 * **For Couchbase:**
 [Step-by-step installation](http://www.couchbase.com/communities/php/getting-started).

Storages comparison
-------------------

**Redis** is the best key-value storage for cache.
Use **Couchbase** if you need fault-tolerant and very easy scalable cluster and if you can afford it ([recommended hardware requirements](http://docs.couchbase.com/couchbase-manual-2.2/#resource-requirements)).
Also, data in Redis and Couchbase storages will be restored even after server reboot.

Differences between the tagging approaches
-------------------

###Approach grouping tags

Fastest method, but there is a possibility of overflow cache.

Input data:

```php

$cache->set('key_1', 'text_1', 0, ['tag_1', 'tag_2']);
$cache->set('key_2', 'text_2', 0, ['tag_1']);
```

View storage:

```
key_1: text_1
key_2: text_2

tag_1: [key_1, key_2]
tag_2: [key_1]
```

Removing tag:

```php

$cache->removeTag('tag_2');
```

View storage:

```
key_2: text_2

tag_1: [key_1, key_2]
```

###Approach versioning

Is the best practice, but slower than the approach with the grouping tags, because when getting the cache containing tags, sent multiple requests to compare versions. There is no cache overflows.

**References**: [nablas by D.Koterov (RUS)](http://dklab.ru/chicken/nablas/47.html) or ["Reset group caches and tagging" by A.Smirnov (RUS)](http://smira.ru/posts/20081029web-caching-memcached-5.html).

Input data:

```php

$cache->set('key_1', 'text_1', 0, ['tag_1', 'tag_2']);
$cache->set('key_2', 'text_2', 0, ['tag_1']);
```

View storage:

```
key_1: [
    value : text_1,
    tags : [
        tag_1 : 0.20782200 1403858079,
        tag_2 : 0.20782200 1403858079
    ]
]
// tag : microtime

key_2: [
    value : text_2,
    tags : [
        tag_1 : 0.20782200 1403858079,
    ]
]

tag_1: 0.20782200 1403858079
tag_2: 0.20782200 1403858079
```

Removing tag:

```php

$cache->removeTag('tag_2');
```

View storage:

```
key_1: [
    value : text_1,
    tags : [
        tag_1 : 0.20782200 1403858079,
        tag_2 : 0.20782200 1403858079
    ]
]
key_2: [
    value : text_2,
    tags : [
        tag_1 : 0.20782200 1403858079,
    ]
]

tag_1: 0.20782200 1403858079
tag_2: 0.29252400 1403858537
```

```php

$cache->get('key_1');
// result: false

```

View storage:

```
key_2: [
    value : text_2,
    tags : [
        tag_1 : 0.20782200 1403858079,
    ]
]

tag_1: 0.20782200 1403858079
```

License
-------------------

The Rock Cache library is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)