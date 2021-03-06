<?php

use Silex\Application;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\TranslationServiceProvider;
use Symfony\Component\Translation\Loader\YamlFileLoader as YamlFileTranslateLoader;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;

$app = new Application();

$app->register(new Neurolit\Silex\Provider\EtherpadServiceProvider());

$app->register(new ServiceControllerServiceProvider());
$app->register(new UrlGeneratorServiceProvider());

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

    $translator->addResource('yaml', __DIR__.'/../config/locales/en_default.yml', 'en');
    $translator->addResource('yaml', __DIR__.'/../config/locales/fr_default.yml', 'fr');

    if (file_exists(__DIR__.'/../config/locales/en.yml')) {
        $translator->addResource('yaml', __DIR__.'/../config/locales/en.yml', 'en');
    }

    if (file_exists(__DIR__.'/../config/locales/fr.yml')) {
        $translator->addResource('yaml', __DIR__.'/../config/locales/fr.yml', 'fr');
    }
    
    return $translator ;
}));

// database
$app->register(new Silex\Provider\DoctrineServiceProvider());

return $app ;
