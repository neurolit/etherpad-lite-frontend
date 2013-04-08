<?php

namespace Notepad\Tests ;

class AppTestCase extends \Silex\WebTestCase
{
  public function createApplication()
  {
    $app = require __DIR__.'/../../../src/app.php';
    require __DIR__.'/../../../config/prod.php';
    require __DIR__.'/../../../src/controllers.php';

    $app['debug'] = true ;
    unset($app['exception_handler']) ;

    // La base de données est à présent une Sqlite en mémoire (non-persistante)
    $app['db.options'] = array(
                               'driver'    => 'pdo_sqlite',
                               'memory'    => true
                               );
    $app['db']->query('CREATE TABLE pad_creation (pad_id VARCHAR(250) NOT NULL PRIMARY KEY, timestamp TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, creator_address VARCHAR(15))') ;

    return $app;
  }

  public function testIndexPage()
  {
    $client = $this->createClient();

    $client->request('GET','/') ;
    $this->assertTrue($client->getResponse()->isOk());
    $indexContent = $client->getResponse()->getContent() ;

    $crawler = $client->request('GET','/fr') ;
    $this->assertTrue($client->getResponse()->isOk());
    $frIndexContent = $client->getResponse()->getContent() ;

    // / et /fr renvoient la même page
    $this->assertEquals($indexContent,$frIndexContent) ;

    $this->assertEquals(1, $crawler->filter('p:contains("texte collaboratif")')->count(),"Subtitle should be in French") ;
    $this->assertEquals(1, $crawler->filter('form')->count()) ;
    $this->assertEquals("fr", $crawler->filter('form > input[type="hidden"][name="lang"]')->attr('value')) ;

    $this->assertEquals(1, $crawler->filter('form button[type="submit"]')->count(),'There should be only one submit button') ;
    $this->assertEquals('createPad', $crawler->filter('form')->attr('action')) ;
    $this->assertEquals('post', $crawler->filter('form')->attr('method')) ;
  }

  public function testEnglishIndexPage()
  {
    $client = $this->createClient();
    $crawler = $client->request('GET','/en') ;
    $this->assertTrue($client->getResponse()->isOk());

    $this->assertEquals(1, $crawler->filter('p:contains("Real-time document collaboration")')->count(),"Subtitle should be in English") ;
    $this->assertEquals(1, $crawler->filter('form')->count()) ;
    $this->assertEquals("en", $crawler->filter('form > input[type="hidden"][name="lang"]')->attr('value')) ;

    $this->assertEquals(1, $crawler->filter('form button[type="submit"]')->count(),'There should be only one submit button') ;
    $this->assertEquals('createPad', $crawler->filter('form')->attr('action')) ;
    $this->assertEquals('post', $crawler->filter('form')->attr('method')) ;
  }

  public function testRegisterEtherpadServiceProvider()
  {

    $protocol = 'ProT';
    $server = 'sERt' ;
    $port = '4567' ;
    $apiKey = 'aAzertyuioP' ;

    $this->app->register(new \Inria\SEISM\Provider\EtherpadServiceProvider(), array(
                                                                                   'etherpad.protocol'     => $protocol,
                                                                                   'etherpad.server'       => $server,
                                                                                   'etherpad.port'         => $port,
                                                                                   'etherpad.api_key'      => $apiKey,
                                                                                   )) ;

    $etherpadLite = $this->app['etherpad'] ;

    $this->assertInstanceOf('Neurolit\EtherpadLite\Client',$etherpadLite) ;

    $this->assertEquals($etherpadLite->getProtocol(),$protocol) ;
    $this->assertEquals($etherpadLite->getServer(),$server) ;
    $this->assertEquals($etherpadLite->getPort(),$port) ;
    $this->assertEquals($etherpadLite->getApiKey(),$apiKey) ;

  }

  public function testReturn404Code()
  {
    $client = $this->createClient();

    $crawler=$client->request('GET','/dummy') ;

    $this->assertTrue($client->getResponse()->isNotFound(),'/dummy should return a 404 error');
  }

  public function testCreatePad()
  {

    $suffix = "SUFFIXE";
    $password = "PASSWORD";

    $etherpadLiteMock = $this->getMock('Inria\SEISM\EtherpadLite', array('createPad'), array(), 'EtherpadLite', false) ;

    // 1. EtherpadLite::createPad ne doit être appelé qu'une fois, avec les bonnes valeurs en paramètres.
    // 2. EtherpadLite::createPad retournera AZERTY comme padId.
    $etherpadLiteMock->expects($this->once())
      ->method('createPad')
      ->with($suffix,$this->anything(),$password)
      ->will($this->returnValue('AZERTY')) ;

    $this->app['etherpad'] = $etherpadLiteMock ;

    $etherpadPublicUrl = "https://testpad.inria.fr/test" ;
    $this->app['etherpad.public_url'] = $etherpadPublicUrl;

    $client = $this->createClient();

    $crawler=$client->request('GET','/') ;

    $createForm = $crawler->selectButton('submit')->form() ;
    $client->submit($createForm,
                    array(
                          'password'=>$password,
                          'padsuffix'=>$suffix
                                ));

    // 3. On doit recevoir une redirection vers Notepad
    // 4. Adresse cible doit être : $protocol://$server:$port/pad/p/AZERTY
    $this->assertTrue($client->getResponse()->isRedirect($etherpadPublicUrl.'/p/AZERTY'),
                      'POST /createPad should redirect to app["etherpad.public_url"]/p/padId') ;

    // 5. Une ligne doit avoir été créée dans la base de données
    $stmt = $this->app['db']->executeQuery('SELECT pad_id from pad_creation') ;

    $results = $stmt->fetchAll() ;

    $this->assertEquals(1,count($results),"A log line should be added in log db") ;
    $this->assertEquals("AZERTY",$results[0]["pad_id"],"The log line should store the pad_id") ;

  }
}
