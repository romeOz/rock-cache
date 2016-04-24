Cache library
====================

[![Latest Stable Version](https://poser.pugx.org/romeOz/rock-cache/v/stable.svg)](https://packagist.org/packages/romeOz/rock-cache)
[![Total Downloads](https://poser.pugx.org/romeOz/rock-cache/downloads.svg)](https://packagist.org/packages/romeOz/rock-cache)
[![Build Status](https://travis-ci.org/romeOz/rock-cache.svg?branch=master)](https://travis-ci.org/romeOz/rock-cache)
[![Coverage Status](https://coveralls.io/repos/romeOz/rock-cache/badge.svg)](https://coveralls.io/r/romeOz/rock-cache)
[![License](https://poser.pugx.org/romeOz/rock-cache/license.svg)](https://packagist.org/packages/romeOz/rock-cache)

What storages can be used:
 
 * [Memcached](http://memcached.org/)
 * [APCu](http://pecl.php.net/package/APCu)
 * [Redis](http://redis.io)
 * [Couchbase](http://www.couchbase.com)
 * [MongoDB](https://www.mongodb.org/)
 * CacheStub (stub for caching) 

All storage objects have one interface, so you can switch them without changing the working code.

Features
-------------------

 * One interface for all storages - you can change storage without changing your code
 * Tagging cache (approach versioning and grouping)
 * Locking - "race condition" ("dog-pile" or "cache miss storm") effects are excluded
 * Serializer for value (json or PHP-serializer)
 * Automatic unserialization
 * Standalone module/component for [Rock Framework](https://github.com/romeOz/rock)

Table of Contents
-------------------

 * [Installation](#installation)
 * [Quick Start](#quick-start)
    - [Memcached](#memcached)
    - [MongoDB](#mongodb)
    - [Locking key](#locking-key)
 * [Documentation](#documentation)
 * [Demo](#demo)
 * [Requirements](#requirements)
 * [Storages comparison](#storages-comparison)
 * [Differences between the approaches a tagging](#differences-between-the-approaches-a-tagging)
    - [Grouping tags](#grouping-tags)
    - [Versioning tags](#versioning-tags)

Installation
-------------------

From the Command Line:

```
composer require romeoz/rock-cache
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

####MongoDB

```php
$connection = new \rock\mongodb\Connection;
$collection = $connection->getCollection('cache')
$collection->createIndex('id', ['unique' => true]);
$collection->createIndex('expire', ['expireAfterSeconds' => 0]); // create TTL index

$config = [
    'storage' => $connection,
    'cacheCollection' => 'cache'
];
$mongoCache = new \rock\cache\MongoCache($config);

...
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
Returns value by key.

####getMulti(array $keys)
Returns multiple values by keys.

####set($key, mixed $value, $expire = 0, array $tags = null)
Sets a value to cache.

####setMulti($key, mixed $value, $expire = 0, array $tags = null)
Sets a multiple key-values to cache.

####add($key, mixed $value, $expire = 0, array $tags = null)
Adds a value to cache.
>Return false, if already exists on the server.

####exists($key)
Checks existence key.

####touch($key, $expire = 0)
Changes expire (TTL) for key.

####touchMulti(array $keys, $expire = 0)
Changes expire (TTL) for multiple keys .

####increment($key, $offset = 1, $expire = 0, $create = true)
Increment a value to cache.

####decrement($key, $offset = 1, $expire = 0, $create = true)
Decrement a value to cache.

####remove($key)
Removes value from cache.

####removeMulti(array $keys)
Removes multiple values from cache.

####getTag($tag)
Returns a keys in accordance with tag.

####getMultiTags(array $tags)
Returns a keys in accordance with multiple tags.

####existsTag($tag)
Checks existence tag.

####removeTag($tag)
Removes a tag.

####removeMultiTag(array $tags)
Removes a multiple tags.

####getAllKeys()
Returns all keys.

>Supported: `Memcached`, `Redis`, `APC`.

####getAll()
Returns all values.

>Supported: `Memcached`, `APC`.

####lock($key)
Sets a lock on the key.

####unlock($key)
Unlocking key.

####flush()
Removes all values from cache.

####status()
Returns a status server.

>Supported: `Memcached`, `Memcache`, `Redis`, `APC`, `Couchbase`.

####getStorage()
Returns a native instance cache-storage.

[Demo](https://github.com/romeOz/docker-rock-cache)
-------------------

 * [Install Docker](https://docs.docker.com/installation/) or [askubuntu](http://askubuntu.com/a/473720)
 * `docker run --name demo -d -p 8080:80 romeoz/docker-rock-cache`
 * Open demo [http://localhost:8080/](http://localhost:8080/)

Requirements
-------------------

You can use each storage separately, requirements are individually for storages.

 * **PHP 5.4-5.6**
>For PHP 7 many of pecl-extensions in development/unstable. 
 * [Redis 2.8+](http://redis.io). Should be installed `apt-get install redis-server` or `docker run --name redis -d -p 6379:6379 romeoz/docker-redis:2.8` (recommended). 
 Also should be installed [PHP extension](http://pecl.php.net/package/redis) `apt-get install php5-redis`
 * [Memcached](http://memcached.org/). Should be installed `apt-get install memcached`  or `docker run --name memcached -d -p 11211:11211 romeoz/docker-memcached` (recommended). 
 Also should be installed php-extension [Memcache](http://pecl.php.net/package/memcache) `apt-get install php5-memcache` or [Memcached](http://pecl.php.net/package/memcached) `apt-get install php5-memcached`.
 * [APCu](http://pecl.php.net/package/APCu). Should be installed `apt-get install php5-apcu`.
 * Couchbase 3.0+. [Step-by-step installation](http://developer.couchbase.com/documentation/server/4.1/sdks/php-2.0/download-links.html) (or [see playbook](https://github.com/romeOz/vagrant-rock-cache/blob/master/provisioning/roles/couchbase/tasks/main.yml)).
 * MongoDB 2.6-3.0. For using [MongoDB](https://www.mongodb.org/) as storage required [Rock MongoDB](https://github.com/romeOz/rock-mongodb): `composer require romeoz/rock-mongodb` 

>All unbolded dependencies is optional

Storages comparison
-------------------

**Redis** is the best key-value storage for cache.
Use **Couchbase** if you need fault-tolerant and very easy scalable cluster and if you can afford it ([recommended hardware requirements](http://docs.couchbase.com/couchbase-manual-2.2/#resource-requirements)).
Also, data in Redis and Couchbase storages will be restored even after server reboot.

Differences between the approaches a tagging
-------------------

###Grouping tags

Fastest method, but there is a possibility of overflow cache.

Set a value:

```php
$cache = new \rock\cache\Memcached;

$cache->set('key_1', 'text_1', 0, ['tag_1', 'tag_2']);
$cache->set('key_2', 'text_2', 0, ['tag_1']);
```

View in memory:

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

View in memory:

```
key_2: text_2

tag_1: [key_1, key_2]
```

###Versioning tags

Is the best practice, but slower than the approach with the grouping tags, because when getting the cache containing tags, sent multiple requests to compare versions. There is no cache overflows.

**References**: [nablas by D.Koterov (RUS)](http://dklab.ru/chicken/nablas/47.html) or ["Reset group caches and tagging" by A.Smirnov (RUS)](http://smira.ru/posts/20081029web-caching-memcached-5.html).

Set a value:

```php
$cache = new \rock\cache\versioning\Memcached;

$cache->set('key_1', 'text_1', 0, ['tag_1', 'tag_2']);
$cache->set('key_2', 'text_2', 0, ['tag_1']);
```

View in memory:

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

View in memory:

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

Returns value:

```php

$cache->get('key_1');
// result: false
```

View in memory:

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