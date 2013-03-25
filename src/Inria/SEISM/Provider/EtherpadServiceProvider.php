<?php
namespace Inria\SEISM\Provider ;

use Silex\Application ;
use Silex\ServiceProviderInterface ;

use Inria\SEISM\EtherpadLite ;

class EtherpadServiceProvider implements ServiceProviderInterface
{
  public function register(Application $app)
  {
    $app['etherpad'] = $app->share(function () use ($app) {
        return new EtherpadLite($app['etherpad.protocol'],
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
