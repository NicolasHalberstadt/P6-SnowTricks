<?php

namespace App\Controller;

use App\Form\VideoType;
use App\Repository\VideoRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/video")
 */
class VideoController extends AbstractController
{
    /**
     * @Route("/{id}/edit", name="video_edit")
     * @param int $id
     * @param VideoRepository $videoRepository
     * @param Request $request
     * @return Response
     */
    public function editVideo(int $id, VideoRepository $videoRepository, Request $request): Response
    {
        $video = $videoRepository->find($id);
        $form = $this->createForm(VideoType::class, $video);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();
        }
        
        return $this->render('video/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }
    
    /**
     * @Route("/{id}/remove", name="video_remove")
     * @param int $id
     * @param VideoRepository $videoRepository
     * @return RedirectResponse
     */
    public function removeVideo(int $id, VideoRepository $videoRepository): RedirectResponse
    {
        $video = $videoRepository->find($id);
        $trick = $video->getTrick();
        $trick->removeVideo($video);
        $em = $this->getDoctrine()->getManager();
        $em->remove($video);
        $em->flush();
        return $this->redirectToRoute('trick_edit', [
            'slug' => $trick->getSlug()
        ]);
    }
    
}
