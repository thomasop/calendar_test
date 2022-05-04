<?php

namespace App\DataFixtures;

use App\Entity\Booking;
use App\Entity\Comment;
use App\Entity\User;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    /** @var UserPasswordHasherInterface */
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
        $user->setEnabled(true);
        $user->setPassword($this->passwordEncoder->hashPassword($user, 'Test1234?'));
        $manager->persist($user);

        for ($i = 1; $i < 10; ++$i) {
            $comment = new Comment();
            $comment->setText('text'.$i);
            $comment->setUser($user);
            $manager->persist($comment);
        }

        $booking = new Booking();
        $booking->setTitle('test');
        $booking->setBeginAt(new DateTime('2022-05-05T15:00:00'));
        $booking->setEndAt(new DateTime('2022-05-05T15:30:00'));
        $booking->setUser($user);
        $manager->persist($booking);
        $manager->flush();
    }
}
