<?php

use rock\cache\Memcached;
use rock\cache\CacheInterface;
use League\Flysystem\Adapter\Local;
use rock\cache\filemanager\FileManager;
use rock\cache\CacheFile;

include_once(__DIR__ . '/vendor/autoload.php');



/** Memcached storage */

$config = [
    'hashKey' => CacheInterface::HASH_MD5, // Default: HASH_MD5
    'serializer' => CacheInterface::SERIALIZE_JSON // Default: SERIALIZE_PHP - php serializator
];
$memcached = new Memcached($config);

$tags = ['tag_1', 'tag_2'];
$value = ['foo', 'bar'];
$expire = 0; // If use expire "0", then time to live infinitely
$memcached->set('key_1', $value, $expire, $tags);

// automatic unserialization
var_dump($memcached->get('key_1')); // result: ['foo', 'bar'];

$memcached->flush(); // Invalidate all items in the cache



/** Local storage */

$adapterConfig = [
    'adapter' => new Local(__DIR__.'/runtime'),
];
$adapter = new FileManager($adapterConfig);

$config = [
    'adapter' => $adapter,
    'hashKey' => CacheInterface::HASH_MD5,
    'serializer' => CacheInterface::SERIALIZE_JSON
];
$cacheFile = new CacheFile($config);

$cacheFile->set('key_1', 'foo');

var_dump($cacheFile->get('key_1')); // result: foo;

$cacheFile->flush(); // Invalidate all items in the cache

