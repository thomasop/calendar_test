<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Comment;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class CommentTest extends KernelTestCase
{
    public function test(): void
    {
        $comment = (new Comment())
        ->setText('gj')
        ->setCreatedAt(new DateTime('2011-01-01T15:03:01.012345Z'));

        self::bootKernel();
        $error = self::getContainer()->get('validator')->validate($comment);
        $this->assertCount(0, $error);
    }

    public function testContent(): void
    {
        $comment = new Comment();
        $content = 'Test content';

        $comment->setText($content);
        $this->assertEquals('Test content', $comment->getText());
    }

    public function testDate(): void
    {
        $comment = new Comment();
        $date = new DateTime('2011-01-01T15:03:01.012345Z');

        $comment->setCreatedAt($date);
        $this->assertEquals(new DateTime('2011-01-01T15:03:01.012345Z'), $comment->getCreatedAt());
    }
}
