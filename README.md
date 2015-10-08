Cache library
====================

[![Latest Stable Version](https://poser.pugx.org/romeOz/rock-cache/v/stable.svg)](https://packagist.org/packages/romeOz/rock-cache)
[![Total Downloads](https://poser.pugx.org/romeOz/rock-cache/downloads.svg)](https://packagist.org/packages/romeOz/rock-cache)
[![Build Status](https://travis-ci.org/romeOz/rock-cache.svg?branch=master)](https://travis-ci.org/romeOz/rock-cache)
[![Coverage Status](https://coveralls.io/repos/romeOz/rock-cache/badge.svg)](https://coveralls.io/r/romeOz/rock-cache)
[![License](https://poser.pugx.org/romeOz/rock-cache/license.svg)](https://packagist.org/packages/romeOz/rock-cache)

[Rock cache on Packagist](https://packagist.org/packages/romeOz/rock-cache)

What storages can be used:
 
 * [Memcached](http://pecl.php.net/package/memcached)
 * [Memcache](http://pecl.php.net/package/memcache)
 * [APCu](http://pecl.php.net/package/APCu)
 * [Redis](http://redis.io)
 * [Couchbase](http://www.couchbase.com)
 * Local (caching to file)
 * CacheStub (stub for caching) 

All storage objects have one interface, so you can switch them without changing the working code.

Features
-------------------

 * One interface for all storages - you can change storage without changing your code
 * Tagging cache (approach versioning and grouping)
 * Locking - "race condition" ("dog-pile" or "cache miss storm") effects are excluded
 * Serializer for value (json or PHP-serializer)
 * Automatic unserialization
 * Stub for caching
 * Stores session data in a key-value storage
 * Standalone module/component for [Rock Framework](https://github.com/romeOz/rock)

Installation
-------------------

From the Command Line:

```
composer require romeoz/rock-cache:*
```

or in your composer.json:

```json
{
    "require": {
        "romeoz/rock-cache": "*"
    }
}

```

Quick Start
-------------------

####Memcached

```php
$config = [
    'hashKey' => CacheInterface::HASH_MD5, // Default: HASH_MD5
    'serializer' => CacheInterface::SERIALIZE_JSON // Default: SERIALIZE_PHP - php serializator
];
$memcached = new \rock\cache\Memcached($config); // or \rock\cache\versioning\Memcached for approach versioning

$tags = ['tag_1', 'tag_2'];
$value = ['foo', 'bar'];
$expire = 0; // If use expire "0", then time to live infinitely
$memcached->set('key_1', $value, $expire, $tags);

// automatic unserialization
$memcached->get('key_1'); // result: ['foo', 'bar'];

$memcached->flush(); // Invalidate all items in the cache
```

####Local storage

```php
use League\Flysystem\Adapter\Local;
use rock\cache\filemanager\FileManager;
use rock\cache\CacheFile;
use rock\cache\CacheInterface;

$adapterConfig = [
    'adapter' => new Local(__DIR__.'/path/to/cache'),
];
$adapter = new FileManager($adapterConfig);

$config = [
    'adapter' => $adapter,
    'hashKey' => CacheInterface::HASH_MD5,
    'serializer' => CacheInterface::SERIALIZE_JSON
];
$cacheFile = new CacheFile($config);

$cacheFile->set('key_1', 'foo');

$memcached->get('key_1'); // result: foo;
```

####Locking key

Race conditions can occur in multi-threaded mode. To avoid the effect, you need to install a lock on the key.

```php
$memcached = new \rock\cache\Memcached

$value = $memcached->get('key_1');
if ($value !== false) {
    return $value;
}

if ($memcached->lock('key_1')) {
    
    // the query to DBMS or other...
    
    $memcached->set('key_1', 'foo');
    $memcached->unlock('key_1');
}
```

Documentation
-------------------

####get($key)
Returns cache by key.

####getMulti(array $keys)
Returns multiple cache by keys.

####set($key, mixed $value, $expire = 0, array $tags = null)
Sets cache.

####setMulti($key, mixed $value, $expire = 0, array $tags = null)
Sets multiple cache.

####add($key, mixed $value, $expire = 0, array $tags = null)
Add cache.
>Return false, if already exists on the server.

####exists($key)
Checks existence cache by key.

####touch($key, $expire = 0)
Changes expire for cache (TTL).

####touchMulti(array $keys, $expire = 0)
Changes expire for multiple cache.

####increment($key, $offset = 1, $expire = 0)
Increment of cache.

####decrement($key, $offset = 1, $expire = 0)
Decrement of cache.

####remove($key)
Removes cache.

####removeMulti(array $keys)
Removes multiple keys.

####getTag($tag)
Returns the keys of cache in accordance with the tag.

####getMultiTags(array $tags)
Returns the keys of cache in accordance with the multiple tags.

####existsTag($tag)
Checks existence tag.

####removeTag($tag)
Removes tag.

####removeMultiTag(array $tags)
Removes multiple tags.

####getAllKeys()
Returns all keys of cache.

>Supported: `Memcached`, `Redis`, `APC`.

####getAll()
Returns all cache.

>Supported: `Memcached`, `APC`.

####lock($key)
Sets a lock on the key.

####Unlock($key)
Unlocking key.

####flush()
Removes all cache.

####status()
Returns status server of cache.

>Supported: `Memcached`, `Memcache`, `Redis`, `APC`, `Couchbase`.

####getStorage()
Returns current cache-storage.


[Demo](https://github.com/romeOz/docker-rock-cache)
-------------------

 * [Install Docker](https://docs.docker.com/installation/) or [askubuntu](http://askubuntu.com/a/473720)
 * `docker run --name demo -d -p 8080:80 romeoz/docker-rock-cache`
 * Open demo [http://localhost:8080/](http://localhost:8080/)

Requirements
-------------------

You can use each storage separately, requirements are individually for storages.

 * PHP 5.4+
 * For Local storage:
 Used library [flysystem](https://github.com/thephpleague/flysystem) which is an filesystem abstraction which allows you to easily swap out a local filesystem for a remote one.
> Note: contains composer.

 * [Redis](http://redis.io) server should be installed `apt-get install redis-server`. Also, should be installed [PHP extension](http://pecl.php.net/package/redis) `apt-get install php5-redis`
 * Memcached/Memcache:
 Memcached demon should be installed `apt-get install memcached`. Also, should be installed php extension [Memcache](http://pecl.php.net/package/memcache) `apt-get install php5-memcache` or [Memcached](http://pecl.php.net/package/memcached) `apt-get install php5-memcached`.
 * [APCu](http://pecl.php.net/package/APCu) should be installed `apt-get install php5-apcu`.
 * Couchbase 3.0: [Step-by-step installation](http://www.couchbase.com/communities/php/getting-started) (or [see playbook](https://github.com/romeOz/vagrant-rock-cache/blob/master/provisioning/roles/couchbase/tasks/main.yml)).
 * Session as memory storage **(optional):** suggested to use [Rock Session](https://github.com/romeOz/rock-session). Should be installed: `composer require romeoz/rock-session:*`

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
$cache = new \rock\cache\Memcached;

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
$cache = new \rock\cache\versioning\Memcached;

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