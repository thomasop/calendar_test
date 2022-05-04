<?php

namespace App\Handler;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Tool\EntityManager;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class FormUserHandler
{
    /** @var MailerInterface */
    private $mailer;
    /** @var EntityManager */
    private $entityManager;
    /** @var UserPasswordHasherInterface */
    private $passwordEncoder;
    /** @var RequestStack */
    private $request;
    /** @var UserRepository */
    private $userRepository;

    public function __construct(MailerInterface $mailer, EntityManager $entityManager, UserPasswordHasherInterface $passwordEncoder, RequestStack $request, UserRepository $userRepository)
    {
        $this->mailer = $mailer;
        $this->entityManager = $entityManager;
        $this->passwordEncoder = $passwordEncoder;
        $this->request = $request;
        $this->userRepository = $userRepository;
    }

    public function update(User $user, FormInterface $form): bool
    {
        $form->handleRequest($this->request->getCurrentRequest());

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $this->entityManager->Add($user);

            return true;
            // do anything else you need here, like send an emai
        }

        return false;
    }

    public function register(User $user, FormInterface $form): bool
    {
        $form->handleRequest($this->request->getCurrentRequest());

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $this->passwordEncoder->hashPassword(
                    $user,
                    $form->get('password')->getData()
                )
            );
            $user->setToken($this->generateToken());
            $this->entityManager->Add($user);
            $message = (new TemplatedEmail())
                ->from('thomasdasilva010@gmail.com')
                ->to(htmlspecialchars($form->get('email')->getData()))
                ->subject('vérification email')

                ->htmlTemplate('user/confirmationEmail.html.twig')
                ->context([
                    'token' => $user->getToken(),
                    'expiration_date' => new \DateTime('+1 days'),
                ])
            ;
            $this->mailer->send($message);

            return true;
            // do anything else you need here, like send an emai
        }

        return false;
    }

    public function forgotPassword(FormInterface $form, User $user): bool
    {
        $form->handleRequest($this->request->getCurrentRequest());
        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();
            $user = $this->userRepository->findOneBy(['email' => $email]);
            if ($user) {
                $user->setToken($this->generateToken());

                $this->entityManager->Add($user);
                $message = (new TemplatedEmail())
                    ->from('thomasdasilva010@gmail.com')
                    ->to(htmlspecialchars($form->get('email')->getData()))
                    ->subject('Mot de passe oublié')

                    ->htmlTemplate('user/forgotpassword.html.twig')
                    ->context([
                        'token' => $user->getToken(),
                        'expiration_date' => new \DateTime('+1 days'),
                    ])
                ;
                $this->mailer->send($message);

                return true;
            }

            return $user;
        }

        return false;
    }

    public function resetPassword(FormInterface $form, User $user): bool
    {
        $form->handleRequest($this->request->getCurrentRequest());
        if ($form->isSubmitted() && $form->isValid()) {
            $user->setToken(null);
            if (preg_match('#^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*\W).{8,}$#', $form->get('password')->getData())) {
                $user->setPassword(
                    $this->passwordEncoder->hashPassword(
                        $user,
                        $form->get('password')->getData()
                    )
                );
                $this->entityManager->Add($user);

                return true;
            } elseif (!preg_match('#^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*\W).{8,}$#', $form->get('password')->getData())) {
                return 'erreur';
            }
        }

        return false;
    }

    public function modifPassword(FormInterface $form, User $user): bool
    {
        $form->handleRequest($this->request->getCurrentRequest());
        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();
            $usermail = $this->userRepository->findOneBy(['email' => $email]);
            if ($user == $usermail) {
                $user->setToken($this->generateToken());

                $this->entityManager->Add($user);
                $message = (new TemplatedEmail())
                    ->from('thomasdasilva010@gmail.com')
                    ->to(htmlspecialchars($form->get('email')->getData()))
                    ->subject('Modifier son mot de passe')

                    ->htmlTemplate('user/modifpassword.html.twig')
                    ->context([
                        'token' => $user->getToken(),
                        'expiration_date' => new \DateTime('+1 hour'),
                    ])
                ;
                $this->mailer->send($message);

                return true;
            }

            return 'erreur';
        }

        return false;
    }

    public function verifyUserEmail(User $userRepository): void
    {
        $userRepository->setEnabled(true);
        $userRepository->setToken(null);
        $this->entityManager->Add($userRepository);
    }

    private function generateToken(): string
    {
        return rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
    }
}
