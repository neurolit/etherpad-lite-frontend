<?php

require_once __DIR__.'/../vendor/autoload.php';
use Symfony\Component\Yaml\Yaml;

$app = new Silex\Application();

if (file_exists(__DIR__."/../conf/app.yml")) {
    $config = Yaml::parse(__DIR__."/../conf/app.yml");
} else {
    $config = Yaml::parse(__DIR__."/../conf/app_default.yml");
}

$app->register(new Inria\SEISM\Provider\EtherpadServiceProvider(), array(
    'etherpad.protocol'     => $config['etherpad']['protocol'],
    'etherpad.server'       => $config['etherpad']['server'],
    'etherpad.port'         => $config['etherpad']['port'],
    'etherpad.api_key'      => $config['etherpad']['api_key'],
    'etherpad.public_url'   => $config['etherpad']['public_url']
                                                                         )) ;

// use Twig
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path'       => __DIR__.'/views',
));

// Use TranslationService
$app->register(new Silex\Provider\TranslationServiceProvider(), array(
    'locale_fallback'           => 'fr',
));

// YAML translation files
use Symfony\Component\Translation\Loader\YamlFileLoader as YamlFileTranslateLoader;
$app['translator'] = $app->share($app->extend('translator',
                                              function($translator, $app){
    $translator->addLoader('yaml', new YamlFileTranslateLoader());

    $translator->addResource('yaml', __DIR__.'/../conf/locales/en_default.yml', 'en');
    $translator->addResource('yaml', __DIR__.'/../conf/locales/fr_default.yml', 'fr');

    if (file_exists(__DIR__.'/../conf/locales/en.yml')) {
        $translator->addResource('yaml', __DIR__.'/../conf/locales/en.yml', 'en');
    }

    if (file_exists(__DIR__.'/../conf/locales/fr.yml')) {
        $translator->addResource('yaml', __DIR__.'/../conf/locales/fr.yml', 'fr');
    }
    
    return $translator ;
}));

// etherpad_log MySQL
// CREATE DATABASE etherpad_log DEFAULT CHARACTER SET 'utf8' DEFAULT COLLATE 'utf8_bin' ;
// CREATE TABLE pad_creation (pad_id VARCHAR(250) NOT NULL PRIMARY KEY, timestamp TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, creator_address VARCHAR(15)) ;
$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'db.options'            => array(
        'driver'    => 'pdo_mysql',
        'host'      => $config['mysql']['host'],
        'dbname'    => $config['mysql']['dbname'],
        'user'      => $config['mysql']['user'],
        'password'  => $config['mysql']['password'],
        'charset'   => 'utf8',
        'driverOptions' => array(PDO::MYSQL_ATTR_INIT_COMMAND
                                 =>
                                 'SET NAMES utf8'),

    ),
                                                                   )) ;

return $app ;
?>
