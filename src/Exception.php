<?php
namespace rock\cache;

use rock\cache\helpers\String;

class Exception extends \Exception
{
    const UNKNOWN_CLASS = 'Unknown class: {class}';
    const UNKNOWN_METHOD = 'Unknown method: {method}';
    const NOT_UNIQUE  = 'Keys must be unique: {data}';
    const INVALID_SAVE = 'Cache invalid save by key: {key}';
    const UNKNOWN_FILE = 'Unknown file: {path}';
    const FILE_EXISTS = 'File exists: {path}';

    /**
     * @param string     $msg
     * @param int        $code
     * @param array      $dataReplace
     * @param \Exception $handler
     */
    public function __construct($msg, $code = 0, array $dataReplace = [], \Exception $handler = null)
    {
        $msg = String::replace($msg, $dataReplace);
        return parent::__construct($msg, $code, $handler);
    }


}