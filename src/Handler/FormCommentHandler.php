<?php

namespace App\Handler;

use App\Tool\EntityManager;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class FormCommentHandler
{
    /** @var TokenStorageInterface */
    private $tokenStorageInterface;
    /** @var EntityManager */
    private $entityManager;
    /** @var RequestStack */
    private $request;

    public function __construct(TokenStorageInterface $tokenStorageInterface, EntityManager $entityManager, RequestStack $request)
    {
        $this->tokenStorageInterface = $tokenStorageInterface;
        $this->entityManager = $entityManager;
        $this->request = $request;
    }

    public function index($form, $comment): bool
    {
        $form->handleRequest($this->request->getCurrentRequest());

        if ($form->isSubmitted() && $form->isValid()) {
            if ($this->tokenStorageInterface->getToken()->getUser()) {
                $comment->setUser($this->tokenStorageInterface->getToken()->getUser());
                $this->entityManager->Add($comment);

                return true;
            }
        }

        return false;
    }

    public function update($form, $comment): bool
    {
        $form->handleRequest($this->request->getCurrentRequest());

        if ($form->isSubmitted() && $form->isValid()) {
            if ($this->tokenStorageInterface->getToken()->getUser()) {
                $comment->setUser($this->tokenStorageInterface->getToken()->getUser());
                $this->entityManager->Add($comment);

                return true;
            }
        }

        return false;
    }

    public function delete($comment): void
    {
        $this->entityManager->Remove($comment);
    }
}
