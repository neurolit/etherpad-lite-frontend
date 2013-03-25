<?php

use Silex\Application;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\TranslationServiceProvider;
use Symfony\Component\Translation\Loader\YamlFileLoader as YamlFileTranslateLoader;

$app = new Application();

$app->register(new Inria\SEISM\Provider\EtherpadServiceProvider());

// use Twig
$app->register(new TwigServiceProvider(), array(
    'twig.path'       => __DIR__.'/../templates',
    'twig.options'    => array('cache' => __DIR__.'/../cache/twig'),
));

// Use TranslationService
$app->register(new TranslationServiceProvider(), array(
    'locale_fallback'           => 'fr',
));

// YAML translation files
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
$app->register(new Silex\Provider\DoctrineServiceProvider());

return $app ;
