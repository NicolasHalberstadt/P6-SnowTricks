<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Trick;
use App\Entity\User;
use App\Repository\CommentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/comment")
 */
class CommentController extends AbstractController
{
    public function add(Trick $trick, Comment $comment, User $user): RedirectResponse
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $comment->setTrick($trick);
        $comment->setUser($user);
        $comment->setCreatedAt(new \DateTime());
        $user->addComment($comment);
        $trick->addComment($comment);
        $em = $this->getDoctrine()->getManager();
        $em->persist($comment);
        $em->flush();
        $this->addFlash('success', 'The comment has been successfully published');
        return $this->redirectToRoute('trick_show', ['slug' => $trick->getSlug()]);
    }
    
    /**
     * @Route("/{id}/remove", name="comment_delete")
     * @param int $id
     * @param CommentRepository $commentRepository
     * @return RedirectResponse
     */
    public function removeComment(int $id, CommentRepository $commentRepository): RedirectResponse
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $comment = $commentRepository->find($id);
        $em = $this->getDoctrine()->getManager();
        $trickSlug = $comment->getTrick()->getSlug();
        $comment->getTrick()->removeComment($comment);
        $comment->getUser()->removeComment($comment);
        $em->remove($comment);
        $em->flush();
        $this->addFlash('success', 'The comment has been successfully removed.');
        return $this->redirectToRoute('trick_show', [
            'slug' => $trickSlug
        ]);
    }
}
