<?php

namespace App\Handler;

use App\Tool\EntityManager;
use App\Tool\SetDate;
use DateInterval;
use DateTime;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class FormBookingHandler
{
    /** @var SetDate */
    private $setDate;
    /** @var EntityManager */
    private $entityManager;
    /** @var RequestStack */
    private $request;
    /** @var TokenStorageInterface */
    private $tokenStorageInterface;

    public function __construct(SetDate $setDate, EntityManager $entityManager, RequestStack $request, TokenStorageInterface $tokenStorageInterface)
    {
        $this->setDate = $setDate;
        $this->entityManager = $entityManager;
        $this->request = $request;
        $this->tokenStorageInterface = $tokenStorageInterface;
    }

    public function edit($start, $end, $booking): void
    {
        $test = substr($start, 0, -16);
        $test2 = substr($end, 0, -16);
        $chaine = substr($test, 4);
        $chaine2 = substr($test2, 4);
        $this->setDate->setDate($chaine, $chaine2, $booking);
    }

    public function editEvent($booking, $form): bool
    {
        $form->handleRequest($this->request->getCurrentRequest());
        if ($form->isSubmitted() && $form->isValid()) {
            $test = $form->get('beginAt')->getData();
            $booking->setBeginAt($test);
            $this->entityManager->Add($booking);
            $booking->setEndAt($test->add(new DateInterval('PT'. 30 .'M')));
            $this->entityManager->Add($booking);

            return true;
        }

        return false;
    }

    public function new($booking, $form, $data): bool
    {
        $form->handleRequest($this->request->getCurrentRequest());
        if ($form->isSubmitted() && $form->isValid()) {
            $currentId = $this->tokenStorageInterface->getToken()->getUser();
            $minutes_to_add = 30;
            $data->add(new DateInterval('PT'.$minutes_to_add.'M'));
            $stamp = $data->format('Y-m-d H:i');
            $datastanp = new DateTime($stamp);
            $booking->setBeginAt($form->get('beginAt')->getData());
            $booking->setUser($currentId);
            $booking->setTitle($currentId);
            $booking->setEndAt($datastanp);
            $this->entityManager->Add($booking);

            return true;
        }
        return false;
    }
}
