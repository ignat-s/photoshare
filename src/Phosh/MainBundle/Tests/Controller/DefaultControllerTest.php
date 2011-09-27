<?php

namespace Phosh\MainBundle\Tests\Controller;

class DefaultControllerTest extends WebTestCase
{
    static protected function loadFixtures()
    {
        self::loadFixturesFromYamlFile(__DIR__ . '/../Resources/fixtures.yml');
    }

    public function testIndexActionAuthenticatedWorks()
    {
        $client = static::createClient(array(), array(
                'PHP_AUTH_USER' => 'admin',
                'PHP_AUTH_PW' => 'adminpass',
            ));

        $crawler = $client->request('GET', '/');

        $this->assertEquals($statusCode = 200, $client->getResponse()->getStatusCode(), "getStatusCode != $statusCode");
        $this->assertTrue($crawler->filter($selector = 'h1:contains("Photoshare")')->count() > 0, "->filter($selector)->count() > 0 is false.");
    }

    public function testIndexActionAnonymousWorks()
    {
        $client = static::createClient(array());

        $crawler = $client->request('GET', '/');

        $this->assertEquals($statusCode = 200, $client->getResponse()->getStatusCode(), "getStatusCode != $statusCode");
        $this->assertTrue($crawler->filter($selector = 'h1:contains("Photoshare")')->count() > 0, "->filter($selector)->count() > 0 is false.");
    }
}