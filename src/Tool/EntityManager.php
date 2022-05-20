<?php

namespace App\Tool;

use Doctrine\ORM\EntityManagerInterface;

class EntityManager
{
    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManger)
    {
        $this->entityManager = $entityManger;
    }

    public function Add(object $entity): void
    {
        $this->entityManager->persist($entity);
        $this->entityManager->flush();
    }

    public function update(): void
    {
        $this->entityManager->flush();
    }

    public function remove(object $entity): void
    {
        $this->entityManager->Remove($entity);
        $this->entityManager->flush();
    }
}
