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
$result_1 = $memcached->get('key_1'); // result: ['foo', 'bar'];

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

$result_2 = $cacheFile->get('key_1'); // result: foo;

$cacheFile->flush(); // Invalidate all items in the cache
?>

<!DOCTYPE html>
<html>
<head>
    <title>Demo Rock cache</title>
    <link href="/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="/assets/css/highlight/github.css" rel="stylesheet">
    <link href="/assets/css/demo.css" rel="stylesheet">

    <script src="/assets/js/jquery.min.js"></script>
    <script src="/assets/js/bootstrap.min.js"></script>
    <script src="/assets/js/highlight.pack.js"></script>
    <script src="/assets/js/demo.js"></script>
</head>
<body>
<div class="container main" role="main">
    <div class="demo-header">
        <h1 class="demo-title">Demo Rock cache</h1>
        <p class="lead demo-description">The example cache.</p>
    </div>
    <div class="demo-main">
        <div class="demo-post-title">
            Memcached storage
        </div>
        <pre><code class="php"><!--
-->use rock\cache\Memcached;
use rock\cache\CacheInterface;

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
var_export($memcached->get('key_1')); // result: ['foo', 'bar'];<!--
--></code></pre>
        Result:
        <pre><code class="html"><?=var_export($result_1)?></code></pre>



        <div class="demo-post-title">
            Local storage
        </div>
        <pre><code class="php"><!--
-->use rock\cache\CacheInterface;
use League\Flysystem\Adapter\Local;
use rock\cache\filemanager\FileManager;
use rock\cache\CacheFile;

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

var_export($cacheFile->get('key_1')); // result: foo;<!--
--></code></pre>
        Result:
        <pre><code class="html"><?=var_export($result_2)?></code></pre>
    </div>
</div>
<div class="demo-footer">
    <p>Demo template built on <a href="http://getbootstrap.com">Bootstrap</a> by <a href="https://github.com/romeOz">@romeo</a>.</p>
    <p>
        <a href="#">Back to top</a>
    </p>
</div>
</body>
</html>