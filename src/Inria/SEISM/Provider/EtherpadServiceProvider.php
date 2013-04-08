<?php
namespace Inria\SEISM\Provider ;

use Silex\Application ;
use Silex\ServiceProviderInterface ;

use Neurolit\EtherpadLite\Client ;

class EtherpadServiceProvider implements ServiceProviderInterface
{
  public function register(Application $app)
  {
    $app['etherpad'] = $app->share(function () use ($app) {
        return new Client($app['etherpad.protocol'],
                          $app['etherpad.server'],
                          $app['etherpad.port'],
                          $app['etherpad.api_key']
                          ) ;
    });
  }

  public function boot(Application $app)
  {
  }
}
?>
