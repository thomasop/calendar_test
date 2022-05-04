<?php

namespace App\tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class LoginControllerTest extends WebTestCase
{
    private $client = null;

    public function testLogin(): void
    {
        $this->client = static::createClient();
        $this->client->request('GET', '/login');
        static::assertEquals(
            Response::HTTP_OK,
            $this->client->getResponse()->getStatusCode()
        );
    }

    public function testForm(): void
    {
        $this->client = static::createClient();

        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('login')->form([
            '_username' => 'admin@mail.com',
            '_password' => 'Test1234?',
        ]);
        $this->client->submit($form);
        $this->client->followRedirect();
        $this->assertSelectorTextContains('h1', 'Accueil');
    }

    public function testLogout(): void
    {
        $this->client = static::createClient();
        $this->client->request('GET', '/logout');
        static::assertEquals(
            Response::HTTP_FOUND,
            $this->client->getResponse()->getStatusCode()
        );
    }
}
