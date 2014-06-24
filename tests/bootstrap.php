<?php
$composerAutoload = dirname(__DIR__) . '/vendor/autoload.php';
if (is_file($composerAutoload)) {
    require_once($composerAutoload);
}

defined('RUNTIME') or define('RUNTIME', __DIR__ . '/runtime');

require_once(__DIR__ . '/TestCase.php');
