<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Form\UserSearchType;
use App\Form\UserType;
use App\Form\UserUpdateFormType;
use App\Form\UserUpdatePasswordFormType;
use App\Handler\FormUserHandler;
use App\Repository\UserRepository;
use App\Tool\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserController extends AbstractController
{
    /** @var FormUserHandler */
    private $formUserHandler;

    public function __construct(FormUserHandler $formUserHandler)
    {
        $this->formUserHandler = $formUserHandler;
    }

    #[route('/user/{id}', name: 'user')]
    #[IsGranted('ROLE_USER', statusCode: 404, message: 'Vous n\'avez pas accés à cette page')]
    public function index(User $user): Response
    {
        return $this->render('user/index.html.twig', [
            'user' => $user,
        ]);
    }

    #[route('/users/display', name: 'userall')]
    #[IsGranted('ROLE_USER', statusCode: 404, message: 'Vous n\'avez pas accés à cette page')]
    public function all(UserRepository $userRepository, Request $request): Response
    {
        $users = null;
        $form = $this->createForm(UserSearchType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $prenom = $form->get('prenom')->getData();
            $nom = $form->get('nom')->getData();
            $users = $userRepository->search($prenom, $nom);
            if (null != $users) {
                $this->addFlash(
                    'success',
                    'Utilisateur trouvé!'
                );
            } else {
                $this->addFlash(
                    'success',
                    'Aucun utilisateur trouvé!'
                );
            }

            return $this->render('user/all.html.twig', [
                'users' => $users,
                'form' => $form->createView(),
            ]);
        }

        return $this->render('user/all.html.twig', [
            'users' => $users,
            'form' => $form->createView(),
        ]);
    }

    #[route('/register', name: 'register')]
    public function register(): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        if (true === $this->formUserHandler->register($user, $form)) {
            $this->addFlash(
                'success',
                'Compte créé, Valider votre comptre grace à votre email !'
            );

            return $this->redirectToRoute('login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[route('/user/update/{id}', name: 'update')]
    #[IsGranted('ROLE_USER', statusCode: 404, message: 'Vous n\'avez pas accés à cette page')]
    public function update(User $user): Response
    {
        $form = $this->createForm(UserUpdateFormType::class, $user);
        if (true === $this->formUserHandler->update($user, $form)) {
            $this->addFlash(
                'success',
                'Pseudo modifié!'
            );

            return $this->redirectToRoute('user', ['id' => $user->getId()]);
        }

        return $this->render('form/formupdateuser.html.twig', [
            'form' => $form->createView(),
            ]);
    }

    #[route('/user/delete/{id}', name: 'user_delete')]
    #[IsGranted('ROLE_USER', statusCode: 404, message: 'Vous n\'avez pas accés à cette page')]
    public function delete(Request $request, User $user, EntityManager $entityManager, TokenStorageInterface $tokenStorage): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $entityManager->remove($user);
        }

        return $this->redirectToRoute('userall', [], Response::HTTP_SEE_OTHER);
    }

    #[route('/user/deleteone/{id}', name: 'user_delete_one')]
    #[IsGranted('ROLE_USER', statusCode: 404, message: 'Vous n\'avez pas accés à cette page')]
    public function deleteOne(Request $request, User $user, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): Response
    {
        if ($this->isCsrfTokenValid('deleteOne'.$user->getId(), $request->request->get('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
            $request->getSession()->invalidate();
            $tokenStorage->setToken();
        }

        return $this->redirectToRoute('login', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/forgot-password', name: 'forgot_password')]
    public function forgotPassword(): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class);
        if (true == $this->formUserHandler->forgotPassword($form, $user)) {
            $this->addFlash(
                'success',
                'Adresse mail trouvé, veuillez verifier votre email pour changer votre mot de passe!'
            );

            return $this->redirectToRoute('login');
        }

        return $this->render('user/forgot.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/reset-password/{token}', name: 'reset_password')]
    public function resetPassword($token, UserRepository $userRepository): Response
    {
        $user = $userRepository->findOneBy(['token' => $token]);
        if ($user) {
            $form = $this->createForm(UserUpdatePasswordFormType::class);
            if (true == $this->formUserHandler->resetPassword($form, $user)) {
                $this->addFlash(
                    'success',
                    'Mot de passe modifié !'
                );

                return $this->redirectToRoute('login');
            } elseif ('erreur' == $this->formUserHandler->resetPassword($form, $user)) {
                $this->addFlash(
                    'success',
                    'Mot de passe incorrect: Une lettre en majuscule, minuscule, un chiffre et caractère speciaux attendu ainsi que 8 caractères minimum!'
                );
            }
        } else {
            $this->addFlash(
                'success',
                'Vous n\'avez pas accès à cette page!'
            );

            return $this->redirectToRoute('login');
        }

        return $this->render('user/reset.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[route('/verification-email/{token}', name: 'app_verify_email')]
    public function verifyUserEmail($token, ManagerRegistry $doctrine): Response
    {
        $entityManager = $doctrine->getManager();
        $userRepository = $entityManager->getRepository(User::class)->findOneBy(['token' => $token]);
        if ($userRepository) {
            $this->formUserHandler->verifyUserEmail($userRepository);
            $this->addFlash(
                'success',
                'compte valide!'
            );

            return $this->redirectToRoute('login');
        }

        return $this->redirectToRoute('login');
    }
}
