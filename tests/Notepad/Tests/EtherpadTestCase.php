<?php

namespace Notepad\Tests ;

use Inria\SEISM\EtherpadLite ;

class EtherpadTestCase extends \PHPUnit_Framework_TestCase
{

  private function _createResponseStub($code,$message,$data="null")
  {
    $responseStub = $this->getMock('Buzz\Message\Response',array('isOk','getContent')) ;

    $responseStub->expects($this->any())
      ->method('isOk')
      ->will($this->returnValue(true)) ;

    $responseStub->expects($this->any())
      ->method('getContent')
      ->will($this->returnValue('{"code": '.$code.', "message":"'.$message.'", "data": '.$data.'}')) ;

    return $responseStub ;
  }

  // constraints : un tableau de tableaux, pour passer plusieurs fois des contraintes à l'appel de get
  // chaque tableau est de la forme (expect_contraint, with_contraint, will_contraint)
  private function _createBrowserMock($constraints)
  {
    $browserMock = $this->getMock('Buzz\Browser',array('get')) ;

    foreach ($constraints as $constraint) {
      $browserMock->expects($constraint[0])
        ->method('get')
        ->with($constraint[1])
        ->will($constraint[2]);
    }

    return $browserMock ;
  }

  private function _createPublicPad($protocol,$server,$port,$apiKey,$suffixe,$texte,$responseStub)
  {
    // Le browser doit être appelé seulement une fois
    $browserMock = $this->_createBrowserMock(array(
                                                   array(
                                                         $this->once(),
                                                         $this->logicalAnd(
                                                                           $this->stringStartsWith($protocol.'://'.$server.':'.$port.'/api/1.2.1/createPad?apikey='.$apiKey.'&padID='),
                                                                           $this->stringEndsWith($suffixe.'&text='.$texte)),
                                                         $this->returnValue($responseStub)
                                                         )
                                                   )
                                             ) ;

    $etherpad = new EtherpadLite($protocol,
                                 $server,
                                 $port,
                                 $apiKey) ;
    $etherpad->setBrowser($browserMock) ;

    return $etherpad->createPad($suffixe,$texte) ;
  }


  /**
   * @dataProvider etherpadConfs
   * @expectedException ErrorException
   * @expectedExceptionMessage Network Unreachable
   */
  public function testCreatePublicPadWithUnreachableNetwork($protocol,$server,$port,$apiKey,$suffixe,$texte)
  {
    $responseStub = $this->getMock('Buzz\Message\Response',array('isOk','getReasonPhrase')) ;

    $responseStub->expects($this->any())
      ->method('isOk')
      ->will($this->returnValue(false)) ;
    
    $responseStub->expects($this->any())
      ->method('getReasonPhrase')
      ->will($this->returnValue('Network Unreachable')) ;

    $etherpad = new EtherpadLite($protocol,
                                 $server,
                                 $port,
                                 $apiKey) ;

    $browserMock = $this->getMock('Buzz\Browser',array('get')) ;

    $browserMock->expects($this->once())
      ->method('get')
      ->will($this->returnValue($responseStub));

    $etherpad->setBrowser($browserMock) ;

    $etherpad->createPad($suffixe,$texte) ;
  }

  /**
   * @dataProvider etherpadConfs
   * @expectedException ErrorException
   * @expectedExceptionMessage Horreur
   */
  public function testCreatePublicPadWithEtherpadError($protocol,$server,$port,$apiKey,$suffixe,$texte)
  {
    // Les réponses sont NOK
    $responseStub = $this->_createResponseStub("1","Horreur");
    $this->_createPublicPad($protocol,$server,$port,$apiKey,$suffixe,$texte,$responseStub) ;
  }

  /**
   * @dataProvider etherpadConfs
   */
  public function testCreatePublicPad($protocol,$server,$port,$apiKey,$suffixe,$texte)
  {
    // Les réponses sont OK
    $responseStub = $this->_createResponseStub("0","ok");
    $this->assertRegexp("/^[a-zA-Z0-9]{16}(_$suffixe)?$/",$this->_createPublicPad($protocol,$server,$port,$apiKey,$suffixe,$texte,$responseStub)) ;
  }

  /**
   * @dataProvider etherpadConfs
   */
  public function testCreateProtectedPad($protocol,$server,$port,$apiKey,$suffixe,$texte)
  {
    // Les réponses sont OK
    $responseStub = $this->_createResponseStub("0","ok",'{"groupID":"g.9cPrs0P4ou9lKjad"}');

    $browserMock = $this->_createBrowserMock(array(
                                                   array(
                                                         $this->at(0),
                                                         $this->stringStartsWith($protocol.'://'.$server.':'.$port.'/api/1.2.1/createGroup?apikey='.$apiKey),
                                                         $this->returnValue($responseStub)),
                                                   array(
                                                         $this->at(1),
                                                         $this->logicalAnd(
                                                                           $this->stringStartsWith($protocol.'://'.$server.':'.$port.'/api/1.2.1/createGroupPad?apikey='.$apiKey.'&groupID=g.9cPrs0P4ou9lKjad&padName='),
                                                                           $this->stringEndsWith($suffixe.'&text='.$texte)),
                                                         $this->returnValue($responseStub)),
                                                   array(
                                                         $this->at(2),
                                                         $this->logicalAnd(
                                                                           $this->stringStartsWith($protocol.'://'.$server.':'.$port.'/api/1.2.1/setPublicStatus?apikey='.$apiKey.'&padID=g.9cPrs0P4ou9lKjad%24'),
                                                                           $this->stringEndsWith('&publicStatus=true')
                                                                           ),
                                                         $this->returnValue($responseStub)),
                                                   array(
                                                         $this->at(3),
                                                         $this->logicalAnd(
                                                                           $this->stringStartsWith($protocol.'://'.$server.':'.$port.'/api/1.2.1/setPassword?apikey='.$apiKey.'&padID=g.9cPrs0P4ou9lKjad%24'),
                                                                           $this->stringEndsWith('&password='."SuperPassword")
                                                                           ),
                                                         $this->returnValue($responseStub))
                                                   )
                                             ) ;
    
    $etherpad = new EtherpadLite($protocol,
                                 $server,
                                 $port,
                                 $apiKey) ;
    $etherpad->setBrowser($browserMock) ;

    $this->assertRegexp("/^g\.9cPrs0P4ou9lKjad\\\$[a-zA-Z0-9]{16}(_$suffixe)?$/",$etherpad->createPad($suffixe,$texte,"SuperPassword")) ;

  }

  /**
   * @dataProvider etherpadConfs
   */
  public function testGetText($protocol,$server,$port,$apiKey,$suffixe,$texte)
  {
    $this->markTestIncomplete(
      'This test has not been implemented yet.'
    );

    $padID = "Brutus";
    $responseStub = $this->_createResponseStub("0","ok", '{"text": "tu quoque mi fili"}');
    $browserMock = $this->_createBrowserMock(array(
                                                   array(
                                                         $this->once(),
                                                         $this->stringStartsWith($protocol.'://'.$server.':'.$port.'/api/1.2.1/getText?apikey='.$apiKey.'&padID='.$padID),
                                                         $this->returnValue($responseStub)
                                                         )
                                                   )
                                             ) ;

    $etherpad = new EtherpadLite($protocol,
                                 $server,
                                 $port,
                                 $apiKey) ;
    $etherpad->setBrowser($browserMock) ;

    return $etherpad->getText($padID) ;
  }

  /**
   * @dataProvider etherpadConfs
   */
  public function testListAllPads($protocol,$server,$port,$apiKey,$suffixe,$texte)
  {
    $responseStub = $this->_createResponseStub("0","ok", '{"padIDs": ["firstPad", "secondPad"]}');
    $browserMock = $this->_createBrowserMock(array(
                                                   array(
                                                         $this->once(),
                                                         $this->stringStartsWith($protocol.'://'.$server.':'.$port.'/api/1.2.1/listAllPads?apikey='.$apiKey),
                                                         $this->returnValue($responseStub)
                                                         )
                                                   )
                                             );

    $etherpad = new EtherpadLite($protocol,
                                 $server,
                                 $port,
                                 $apiKey);
    $etherpad->setBrowser($browserMock);

    $pads = $etherpad->listAllPads();

    $this->assertEquals(2, sizeOf($pads));
    $this->assertEquals("firstPad",$pads[0]);
    $this->assertEquals($pads, array("firstPad","secondPad"));
  }

  public function etherpadConfs()
  {
    return array(
                 array('http','localhost','9001','apiKey1','suffixe','texte1'),
                 array('https','www.inria.fr','443','apiKey4','','texte5'),
                 array('https','www.inria.fr','443','apiKey4','',''),
                 array('https','www.inria.fr','443','apiKey4','suffixe',''),
                 );
  }

}

?>
