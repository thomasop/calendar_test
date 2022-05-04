<?php

namespace App\Controller;

use App\Entity\Booking;
use App\Handler\FormBookingHandler;
use App\Repository\BookingRepository;
use App\Tool\EntityManager;
use App\Tool\SetDate;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Polyfill\Intl\Icu\IntlDateFormatter;

#[route('/réservation')]
class BookingController extends AbstractController
{
    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var FormBookingHandler */
    private $formBookingHandler;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        FormBookingHandler $formBookingHandler,
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->formBookingHandler = $formBookingHandler;
    }

    #[route('/calendrier', name: 'booking_calendar', methods: ["GET", "POST"])]
    #[IsGranted('ROLE_USER', statusCode: 404, message: 'Vous n\'avez pas accés à cette page')]
    public function calendar(): Response
    {
        $currentId = $this->tokenStorage->getToken()->getUser();

        return $this->render('booking/calendar.html.twig', [
            'user' => $currentId,
        ]);
    }

    #[route('/', name: 'booking_index', methods: ["GET"])]
    #[IsGranted('ROLE_ADMIN', statusCode: 404, message: 'Vous n\'avez pas accés à cette page')]
    public function index(BookingRepository $bookingRepository): Response
    {
        return $this->render('booking/index.html.twig', [
            'bookings' => $bookingRepository->findAll(),
        ]);
    }

    #[route('/{id}', name: 'booking_show', methods: ["GET"])]
    #[IsGranted('ROLE_USER', statusCode: 404, message: 'Vous n\'avez pas accés à cette page')]
    public function show(Booking $booking): Response
    {
        if ($this->tokenStorage->getToken()->getUser() == $booking->getUser()) {
            return $this->render('booking/show.html.twig', [
                'booking' => $booking,
            ]);
        }
        $this->addFlash(
            'success',
            'Vous n\'avez pas accès a cette page !'
        );

        return $this->redirectToRoute('booking_calendar');
    }

    #[route('/{id}/edit/{start}/{end}', name: 'booking_edit')]
    #[IsGranted('ROLE_USER', statusCode: 404, message: 'Vous n\'avez pas accés à cette page')]
    public function edit(Booking $booking, $start, $end, SetDate $setDate): Response
    {
        if ($this->tokenStorage->getToken()->getUser() == $booking->getUser()) {
            $this->formBookingHandler->edit($start, $end, $booking);
            $this->addFlash(
                'success',
                'Rendez-vous modifié !'
            );

            return $this->redirectToRoute('booking_calendar');
        }
        $this->addFlash(
            'success',
            'Vous n\'avez pas accès a cette page !'
        );

        return $this->redirectToRoute('booking_calendar');
    }

    #[route('/{id}/edit', name: 'booking_edit_event')]
    #[IsGranted('ROLE_USER', statusCode: 404, message: 'Vous n\'avez pas accés à cette page')]
    public function editEvent(Booking $booking, Request $request, EntityManager $entityManager): Response
    {
        if ($this->tokenStorage->getToken()->getUser() == $booking->getUser()) {
            $form = $this->createFormBuilder()
                ->add('beginAt', DateTimeType::class, ['label' => 'Heure : ', 'data' => $booking->getBeginAt(), 'html5' => false, 'date_format' => IntlDateFormatter::LONG,  'widget' => 'choice', 'minutes' => [0, 30]])
                ->getForm();
            if (true === $this->formBookingHandler->editEvent($booking, $form)) {
                $this->addFlash(
                    'success',
                    'Rendez-vous modifié !'
                );

                return $this->redirectToRoute('booking_calendar', [], Response::HTTP_SEE_OTHER);
            }

            return $this->render('booking/editevent.html.twig', [
                'form' => $form->createView(),
                'booking' => $booking,
            ]);
        }
        $this->addFlash(
            'success',
            'Vous n\'avez pas accès a cette page !'
        );

        return $this->redirectToRoute('booking_calendar');
    }

    #[route('/{id}', name: 'booking_delete', methods: ["POST"])]
    #[IsGranted('ROLE_USER', statusCode: 404, message: 'Vous n\'avez pas accés à cette page')]
    public function delete(Request $request, Booking $booking, EntityManager $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$booking->getId(), $request->request->get('_token'))) {
            $entityManager->remove($booking);
            $this->addFlash(
                'success',
                'Rendez-vous supprimé !'
            );
        }
        return $this->redirectToRoute('booking_calendar', [], Response::HTTP_SEE_OTHER);
    }

    #[route('/new/{date}', name: 'booking_new')]
    #[IsGranted('ROLE_USER', statusCode: 404, message: 'Vous n\'avez pas accés à cette page')]
    public function new($date, Request $request, EntityManagerInterface $entityManager, SetDate $setDate)
    {
        $data = $setDate->setOne($date);
        $now = new DateTime('now');
        if (($data instanceof DateTime) && ($data->format('Y') >= $now->format('Y'))) {
            $booking = new Booking();
            $form = $this->createFormBuilder()
                ->add('beginAt', DateTimeType::class, ['label' => 'Heure : ', 'data' => $data, 'html5' => false, 'date_format' => IntlDateFormatter::LONG, 'widget' => 'choice', 'minutes' => [0, 30]])
                ->getForm();
            if (true === $this->formBookingHandler->new($booking, $form, $data)) {
                $this->addFlash(
                    'success',
                    'Rendez-vous ajouté !'
                );

                return $this->redirectToRoute('booking_calendar', [], Response::HTTP_SEE_OTHER);
            }

            return $this->render('booking/new.html.twig', [
                'form' => $form->createView(),
                'booking' => $booking,
            ]);
        }

        return $this->redirectToRoute('booking_calendar');
    }
}
