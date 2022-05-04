<?php

namespace App\tests\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class CommentControllerTest extends WebTestCase
{
    private $client = null;

    public function testIndex()
    {
        $this->client = static::createClient();
        $crawler = $this->client->request('GET', '/commentaires');
        static::assertEquals(
        Response::HTTP_OK,
        $this->client->getResponse()->getStatusCode()
        );
    }

    public function testFormIndex(): void
    {
        $this->client = static::createClient();

        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('admin@mail.com');

        $this->client->loginUser($testUser);
        $crawler = $this->client->request('GET', '/commentaires');
        $buttonCrawlerNode = $crawler->selectButton('add');
        $form = $buttonCrawlerNode->form();
        $form['comment_form[text]'] = 'test';
        $this->client->submit($form);
        $this->assertResponseRedirects();
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert.alert-success');
    }

    public function testUpdate()
    {
        $this->client = static::createClient();

        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('admin@mail.com');

        $this->client->loginUser($testUser);
        $crawler = $this->client->request('GET', '/commentaire/modification/1');
        static::assertEquals(
        Response::HTTP_OK,
        $this->client->getResponse()->getStatusCode()
        );
    }

    public function testFormUpdate(): void
    {
        $this->client = static::createClient();

        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('admin@mail.com');

        $this->client->loginUser($testUser);
        $crawler = $this->client->request('GET', '/commentaire/modification/1');
        $buttonCrawlerNode = $crawler->selectButton('comment_update');
        $form = $buttonCrawlerNode->form();
        $form['comment_form[text]'] = 'test';
        $this->client->submit($form);
        $this->assertResponseRedirects();
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert.alert-success');
    }

    public function testDelete()
    {
        $this->client = static::createClient();

        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('admin@mail.com');

        $this->client->loginUser($testUser);
        $crawler = $this->client->request('GET', '/commentaire/suppression/1');
        static::assertEquals(
        Response::HTTP_FOUND,
        $this->client->getResponse()->getStatusCode()
        );
    }
}