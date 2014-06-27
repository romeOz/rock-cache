<?php
$composerAutoload = dirname(__DIR__) . '/vendor/autoload.php';
if (is_file($composerAutoload)) {
    require_once($composerAutoload);
}

spl_autoload_register(function($class) {
        $first = substr($class, 0, 8);
        if ($first !== 'rockunit') {
            return;
        }
        $class = str_replace('\\', '/', str_replace($first, '', $class));
        $location = __DIR__ . '/' . $class . '.php';

        if (is_file($location)) {
            require_once($location);
        }
    });

defined('RUNTIME') or define('RUNTIME', __DIR__ . '/runtime');
