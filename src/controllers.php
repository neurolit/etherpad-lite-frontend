<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

// Error pages
$app->error(function (\Exception $e, $code) use ($app) {
    if ($app['debug']) {
        return;
    }

    $app['locale'] = "fr" ;

    $page = 404 == $code ? '404.html' : '500.html';

    return new Response($app['twig']->render($page, array('code' => $code)), $code);
});

// /fr et /en
$app->get('/{lang}',
          function ($lang, Silex\Application $app, Request $request) {
            $app['translator']->setLocale($lang) ;
            return $app['twig']->render('index.html',
                                        array(
                                              'lang' => $lang,
                                              ));
          })
->value('lang', 'fr')
->assert('lang', 'fr|en');

// /createPad
$app->post('/createPad',
           function (Silex\Application $app, Request $request) {
             $app['translator']->setLocale($request->get('lang')) ;

             $suffix = $request->get('padsuffix') ;
             $password = $request->get('password') ;
             $lang = $request->get('lang') ;

             $text = $app['frontend.pad_initial_text'] ;

             try {
               $padId = $app['etherpad']->createPad($suffix,$text,$password) ;
               $app['db']->executeUpdate('INSERT INTO pad_creation ( pad_id, creator_address, creator_inria_login ) VALUES ( ?, ?, ? )',
                                         array($padId, $request->getClientIp(), $request->getUser())) ;

               return $app->redirect($app['etherpad.public_url']."/p/$padId") ;

             } catch (Exception $e) {
               return $app['twig']->render('index.html',
                                           array(
                                                 'lang' => $lang,
                                                 'error' => $e->getMessage(),
                                                 'padsuffix' => $suffix )
                                           );
             }
           });

return $app ;
?>
