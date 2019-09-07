<?php

namespace Tests\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertContains('Welcome to Symfony', $crawler->filter('#container h1')->text());
    }
    
    public function testInputMark()
    {
        //Make sure when go to mark_input page, it load only the required student (the one related
        //to the rigth section
        $client = static::createClient();
        //Don't forget the path of the route (/mark_input/{section_id}/{evaluation_id}
        //And notice that section 1 is 1ere G3B; that's why KOYASSANGOU must be part of it
        $crawler = $client->request('GET', '/mark_input/2/1');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertContains('KOYASSANGOU', $client->getResponse()->getContent());
        $crawler = $client->request('GET', '/mark_input/1/1');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertNotContains('KOYASSANGOU', $client->getResponse()->getContent());
        $crawler = $client->request('GET', '/mark_input/1/1');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertContains('Eleve name_1', $client->getResponse()->getContent());
        
        
    }
    
    
}
