<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Form\CommentFormType;
use App\Handler\FormCommentHandler;
use App\Repository\CommentRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class CommentController extends AbstractController
{
    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var FormCommentHandler */
    private $formCommentHandler;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        FormCommentHandler $formCommentHandler
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->formCommentHandler = $formCommentHandler;
    }

    #[route('/commentaires', name: 'comment')]
    public function index(CommentRepository $commentRepository, Request $request): Response
    {
        $comment = new Comment();
        $offset = max(0, $request->query->getInt('offset', 0));
        $paginator = $commentRepository->getCommentPaginator($offset);
        $form = $this->createForm(CommentFormType::class, $comment);
        if (true == $this->formCommentHandler->index($form, $comment)) {
            $this->addFlash(
                'success',
                'commentaire créer!'
            );

            return $this->redirectToRoute('comment');
        }

        return $this->render('comment/index.html.twig', [
            'form' => $form->createView(),
            'comments' => $paginator,
            'precedant' => $offset - CommentRepository::PAGINATOR_PER_PAGE,
            'suivant' => min(count($paginator), $offset + CommentRepository::PAGINATOR_PER_PAGE),
        ]);
    }

    #[route('/commentaire/modification/{id}', name: 'comment_update')]
    #[IsGranted('ROLE_USER', statusCode: 404, message: 'Vous n\'avez pas accés à cette page')]
    public function update(Comment $comment): Response
    {
        if ($this->tokenStorage->getToken()->getUser() == $comment->getUser() || $this->tokenStorage->getToken()->getUser()->getRoles() == ['ROLE_ADMIN']) {
            $form = $this->createForm(CommentFormType::class, $comment);
            if (true == $this->formCommentHandler->update($form, $comment)) {
                $this->addFlash(
                    'success',
                    'commentaire modifié !'
                );

                return $this->redirectToRoute('comment');
            }

            return $this->render('comment/update.html.twig', [
                'form' => $form->createView(),
            ]);
        }
        $this->addFlash(
            'success',
            'Vous n\'avez pas accès a cette page !'
        );

        return $this->redirectToRoute('comment');
    }

    #[route('/commentaire/suppression/{id}', name: 'comment_delete')]
    #[IsGranted('ROLE_USER', statusCode: 404, message: 'Vous n\'avez pas accés à cette page')]
    public function delete(Comment $comment, Request $request): Response
    {
        if ($this->tokenStorage->getToken()->getUser() == $comment->getUser() || $this->tokenStorage->getToken()->getUser()->getRoles() == ['ROLE_ADMIN']) {
            if ($this->isCsrfTokenValid('delete'.$comment->getId(), $request->request->get('_token'))) {
                $this->formCommentHandler->delete($comment);
                $this->addFlash(
                    'success',
                    'Avis supprimé!'
                );
            }
        }
        $this->addFlash(
            'success',
            'Vous n\'avez pas accès a cette page !'
        );

        return $this->redirectToRoute('comment');
    }
}
