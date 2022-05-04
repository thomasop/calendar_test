<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Booking;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class BookingTest extends KernelTestCase
{
    public function test(): void
    {
        $booking = (new Booking())
        ->setTitle('gj')
        ->setBeginAt(new DateTime('2011-01-01T15:03:01.012345Z'))
        ->setEndAt(new DateTime('2011-01-01T15:03:01.012345Z'));

        self::bootKernel();
        $error = self::getContainer()->get('validator')->validate($booking);
        $this->assertCount(0, $error);
    }

    public function testTitle(): void
    {
        $booking = new Booking();
        $title = 'title';

        $booking->setTitle($title);
        $this->assertEquals('title', $booking->getTitle());
    }

    public function testDateStart(): void
    {
        $booking = new Booking();
        $date = new DateTime('2011-01-01T15:03:01.012345Z');

        $booking->setBeginAt($date);
        $this->assertEquals(new DateTime('2011-01-01T15:03:01.012345Z'), $booking->getBeginAt());
    }

    public function testDateEnd(): void
    {
        $booking = new Booking();
        $date = new DateTime('2011-01-01T15:03:01.012345Z');

        $booking->setEndAt($date);
        $this->assertEquals(new DateTime('2011-01-01T15:03:01.012345Z'), $booking->getEndAt());
    }
}
