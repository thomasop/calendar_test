<?php

namespace App\Controller;

use App\Form\ContactFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'app_contact')]
    public function index(Request $request, MailerInterface $mailer): Response
    {
        $form = $this->createForm(ContactFormType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $message = (new Email())
                ->from(htmlspecialchars($form->get('email')->getData()))
                ->to('thomasdasilva010@gmail.com')
                ->subject(htmlspecialchars($form->get('sujet')->getData()))
                ->text('Prenom : '.htmlspecialchars($form->get('prenom')->getData()).' Nom : '.htmlspecialchars($form->get('nom')->getData()).' Message : '.htmlspecialchars($form->get('content')->getData()))
                ;
            $mailer->send($message);
        }

        return $this->render('contact/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
