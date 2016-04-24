<?php

namespace rockunit;

use rock\base\Alias;
use rock\mongodb\MongoException;

trait MongoDbTestCase
{
    public static $params;
    /**
     * @var array Mongo connection configuration.
     */
    protected $mongoDbConfig = [
        'dsn' => "mongodb://localhost:27017",
        'defaultDatabaseName' => 'rocktest',
        'options' => [],
    ];
    /**
     * @var \rock\mongodb\Connection Mongo connection instance.
     */
    protected $mongodb;

    protected function tearDown()
    {
        if ($this->mongodb) {
            $this->mongodb->close();
        }
    }

    public function init($serialize)
    {
    }

    /**
     * @param  boolean                 $reset whether to clean up the test database
     * @param  boolean                 $open  whether to open test database
     * @return \rock\mongodb\Connection
     */
    public function getConnection($reset = false, $open = true)
    {
        if (!$reset && $this->mongodb) {
            return $this->mongodb;
        }

        $config = self::getParam('mongodb');

        if (!empty($config)) {
            $this->mongoDbConfig = $config;
        }
        $connection = new \rock\mongodb\Connection;
        $connection->dsn = "mongodb://travis:test@mongodb:27017";

        $connection->defaultDatabaseName = $this->mongoDbConfig['defaultDatabaseName'];
        if (isset($this->mongoDbConfig['options'])) {
            $connection->options = $this->mongoDbConfig['options'];
        }
        if ($open) {
            $connection->open();
        }
        $this->mongodb = $connection;

        return $connection;
    }

    /**
     * Drops the specified collection.
     * @param string $name collection name.
     */
    protected function dropCollection($name)
    {
        if ($this->mongodb) {
            try {
                $this->mongodb->getCollection($name)->drop();
            } catch (MongoException $e) {
                // shut down exception
            }
        }
    }

    /**
     * Drops the specified file collection.
     * @param string $name file collection name.
     */
    protected function dropFileCollection($name = 'fs')
    {
        if ($this->mongodb) {
            try {
                $this->mongodb->getFileCollection($name)->drop();
            } catch (MongoException $e) {
                // shut down exception
            }
        }
    }

    /**
     * Finds all records in collection.
     * @param  \rock\mongodb\Collection $collection
     * @param  array                   $condition
     * @param  array                   $fields
     * @return array                   rows
     */
    protected function findAll($collection, $condition = [], $fields = [])
    {
        $cursor = $collection->find($condition, $fields);
        $result = [];
        foreach ($cursor as $data) {
            $result[] = $data;
        }

        return $result;
    }

    /**
     * Returns the Mongo server version.
     * @return string Mongo server version.
     */
    protected function getServerVersion()
    {
        $connection = $this->getConnection();
        $buildInfo = $connection->getDatabase()->executeCommand(['buildinfo' => true]);

        return $buildInfo['version'];
    }

    /**
     * Returns a test configuration param from /data/config.php
     * @param  string $name    params name
     * @param  mixed  $default default value to use when param is not set.
     * @return mixed  the value of the configuration param
     */
    public static function getParam($name, $default = null)
    {
        if (static::$params === null) {
            static::$params = require(Alias::getAlias('@rockunit/data/config.php'));
        }

        return isset(static::$params[$name]) ? static::$params[$name] : $default;
    }
}
