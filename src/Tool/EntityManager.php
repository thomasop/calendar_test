<?php

namespace App\Tool;

use Doctrine\ORM\EntityManagerInterface;

class EntityManager
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManger)
    {
        $this->entityManager = $entityManger;
    }

    public function Add(object $entity)
    {
        $this->entityManager->persist($entity);
        $this->entityManager->flush();
    }

    public function update()
    {
        $this->entityManager->flush();
    }

    public function remove(object $entity)
    {
        $this->entityManager->Remove($entity);
        $this->entityManager->flush();
    }
}
