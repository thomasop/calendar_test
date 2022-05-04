<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Comment;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private $passwordEncoder;

    public function __construct(UserPasswordHasherInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setPrenom('admin');
        $user->setNom('nom');
        $user->setRoles(['ROLE_ADMIN']);
        $user->setEmail('admin@mail.com');
        $user->setPassword($this->passwordEncoder->hashPassword($user, 'Test1234?'));
        $manager->persist($user);

        for ($i = 1; $i < 10; ++$i) {
            $comment = new Comment();
            $comment->setText('text'.$i);
            $comment->setUser($user);
            $manager->persist($comment);
        }
        // $product = new Product();
        // $manager->persist($product);

        $manager->flush();
    }
}