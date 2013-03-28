<?php

use Symfony\Component\Yaml\Yaml;

if (file_exists(__DIR__."/app.yml")) {
    $config = Yaml::parse(__DIR__."/app.yml");
} else {
    $config = Yaml::parse(__DIR__."/app_default.yml");
}

$app['etherpad.protocol']   = $config['etherpad']['protocol'];
$app['etherpad.server']     = $config['etherpad']['server'];
$app['etherpad.port']       = $config['etherpad']['port'];
$app['etherpad.api_key']    = $config['etherpad']['api_key'];
$app['etherpad.public_url'] = $config['etherpad']['public_url'];

$app['db.options'] = array(
    'driver'    => 'pdo_mysql',
    'host'      => $config['mysql']['host'],
    'dbname'    => $config['mysql']['dbname'],
    'user'      => $config['mysql']['user'],
    'password'  => $config['mysql']['password'],
    'charset'   => 'utf8',
    'driverOptions' => array(PDO::MYSQL_ATTR_INIT_COMMAND
                             =>
                             'SET NAMES utf8'),
);

$app['frontend.pad_initial_text'] = $config['frontend']['pad_initial_text'];
