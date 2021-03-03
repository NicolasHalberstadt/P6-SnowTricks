<?php

namespace App\Controller;

use App\Repository\ImageRepository;
use App\Repository\TrickRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/image")
 */
class ImageController extends AbstractController
{
    /**
     * @Route("/{id}/remove", name="image_remove")
     * @param int $id
     * @param ImageRepository $imageRepository
     * @param TrickRepository $trickRepository
     * @return Response
     */
    public function removeImage(int $id, ImageRepository $imageRepository, TrickRepository $trickRepository): Response
    {
        $image = $imageRepository->find($id);
        $trick = $trickRepository->find($image->getTrick()->getId());
        if (!$image) {
            $this->addFlash('danger', 'No image have been found.');
            return $this->redirectToRoute('trick_edit', [
                'slug' => $trick->getSlug()
            ]);
        }
        
        // delete image from it's trick's folder
        $imagePath = $this->getParameter('tricks_directory') . '/' . $trick->getSlug() . '/' . $image->getName();
        $fs = new Filesystem();
        $fs->remove($imagePath);
        
        $trick->removeImage($image);
        $this->getDoctrine()->getManager()->remove($image);
        $this->getDoctrine()->getManager()->flush();
        return $this->redirectToRoute('trick_edit', [
            'slug' => $trick->getSlug()
        ]);
    }
}
