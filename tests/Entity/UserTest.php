<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserTest extends KernelTestCase
{
    public function test(): void
    {
        $user = (new User())
        ->setPrenom('gj')
        ->setNom('nom')
        ->setEmail('mail@mail.com')
        ->setPassword('Test1234?');

        self::bootKernel();
        $error = self::getContainer()->get('validator')->validate($user);
        $this->assertCount(0, $error);
    }

    public function testPrenom(): void
    {
        $user = new User();
        $prenom = 'prenom';

        $user->setPrenom($prenom);
        $this->assertEquals('prenom', $user->getPrenom());
    }

    public function testNom(): void
    {
        $user = new User();
        $nom = 'nom';

        $user->setNom($nom);
        $this->assertEquals('nom', $user->getNom());
    }

    public function testMail(): void
    {
        $user = new User();
        $mail = 'nom@mail.com';

        $user->setEmail($mail);
        $this->assertEquals('nom@mail.com', $user->getEmail());
    }

    public function testPassword(): void
    {
        $user = new User();
        $password = 'Test1234?';

        $user->setPassword($password);
        $this->assertEquals('Test1234?', $user->getPassword());
    }
}
