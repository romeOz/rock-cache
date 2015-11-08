<?php
return [

    'mongodb' => [
        'dsn' => "mongodb://travis:test@{$_SERVER["MONGODB_PORT_27017_TCP_ADDR"]}:27017",
        'defaultDatabaseName' => 'rocktest',
        'options' => [],
    ],
];
