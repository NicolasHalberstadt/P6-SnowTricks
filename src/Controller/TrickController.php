<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Image;
use App\Entity\Trick;
use App\Entity\User;
use App\Form\CommentType;
use App\Form\TrickFormType;
use App\Repository\TrickRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * @Route("/trick")
 */
class TrickController extends AbstractController
{
    
    /**
     * @var SluggerInterface
     */
    private $slugger;
    /**
     * @var EntityManagerInterface;
     */
    private $em;
    
    public function __construct(SluggerInterface $slugger, EntityManagerInterface $em)
    {
        $this->slugger = $slugger;
        $this->em = $em;
    }
    
    /**
     * @Route("/{slug}", name="trick_show")
     * @param string $slug
     * @param TrickRepository $trickRepository
     * @param Request $request
     * @return Response
     */
    public function showTrick(string $slug, TrickRepository $trickRepository, Request $request): Response
    {
        $trick = $trickRepository->findOneBy(['slug' => $slug]);
        if (empty($trick)) {
            $this->addFlash('danger', 'No trick has been found with this slug');
            return $this->redirectToRoute('home');
        }
        
        // Add comment
        $comment = new Comment();
        $commentForm = $this->createForm(CommentType::class, $comment);
        $commentForm->handleRequest($request);
        $user = $this->getUser();
        if ($commentForm->isSubmitted() && $commentForm->isValid()) {
            return $this->forward('App\Controller\CommentController::add', [
                'commentForm' => $commentForm,
                'trick' => $trick,
                'comment' => $comment,
                'user' => $user
            ]);
        }
        $comments = $trick->getComments();
        return $this->render('trick/show.html.twig', [
            'trick' => $trick,
            'commentForm' => $commentForm->createView(),
            'comments' => $comments
        ]);
    }
    
    /**
     * @Route("/add", name="trick_add", priority=2)
     * @param Request $request
     * @return Response
     */
    public function addTrick(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $trick = new Trick();
        $form = $this->createForm(TrickFormType::class, $trick);
        $form->handleRequest($request);
        /** @var User $user */
        $user = $this->getUser();
        
        if ($form->isSubmitted() && $form->isValid()) {
            
            //slug trick's name for url
            $trick->setName(ucfirst(strtolower($form->get('name')->getData())));
            $trick->setSlug($this->slugger->slug(strtolower($trick->getName())));
            $trick->setCreatedAt(new \DateTime());
            $trick->setUser($user);
            // get images from $form and uploads them into their trick's directory
            $images = $form->get('images')->getData();
            if ($images) {
                $this->addImages($images, $trick);
            }
            $videosUrl = $form->get('videos')->getData();
            if ($videosUrl) {
                $this->addVideos($videosUrl, $trick);
            }
            $this->em->persist($trick);
            $this->em->flush();
            $this->addFlash('success', 'The trick has been successfully added');
            return $this->redirectToRoute('trick_show', ['slug' => $trick->getSlug()]);
        }
        
        return $this->render('trick/add.html.twig', [
            'form' => $form->createView()
        ]);
        
    }
    
    /**
     * @Route("/{slug}/edit", name="trick_edit")
     * @param string $slug
     * @param TrickRepository $trickRepository
     * @param Request $request
     * @return Response
     */
    public function editTrick(string $slug, TrickRepository $trickRepository, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $trick = $trickRepository->findOneBy(['slug' => $slug]);
        $form = $this->createForm(TrickFormType::class, $trick);
        $oldTrickName = $trick->getName();
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            // rename trick's image folder if trick's name change
            if ($form->get('name')->getData() !== $oldTrickName) {
                $fs = new Filesystem();
                $oldDir = $this->getParameter('tricks_directory') . '/' . $trick->getSlug();
                $trick->setSlug(strtolower($this->slugger->slug($trick->getName())));
                if ($fs->exists($oldDir)) {
                    $newDir = $this->getParameter('tricks_directory') . '/' . $trick->getSlug();
                    $fs->rename($oldDir, $newDir);
                }
            }
            $images = $form->get('images')->getData();
            if ($images) {
                $this->addImages($images, $trick);
            }
            $videosUrl = $form->get('videos')->getData();
            if ($videosUrl) {
                $this->addVideos($videosUrl, $trick);
            }
            $trick->setName(ucfirst(strtolower($form->get('name')->getData())));
            $this->em->persist($trick);
            $this->em->flush();
            $this->addFlash('success', 'The trick has been successfully updated');
            return $this->redirectToRoute('trick_show', ['slug' => $trick->getSlug()]);
        }
        
        return $this->render('trick/edit.html.twig', [
            'form' => $form->createView(),
            'trick' => $trick
        ]);
    }
    
    /**
     * @Route("/{slug}/delete", name="trick_delete")
     * @param string $slug
     * @param TrickRepository $trickRepository
     * @return RedirectResponse
     */
    public function deleteTrick(
        string $slug,
        TrickRepository $trickRepository
    ): RedirectResponse {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $trick = $trickRepository->findOneBy(['slug' => $slug]);
        if (!$trick) {
            $this->addFlash('danger', 'No trick can be found');
            return $this->redirectToRoute('home');
        }
        $fs = new Filesystem();
        $dir = $this->getParameter('tricks_directory') . '/' . $trick->getSlug();
        if ($fs->exists($dir)) {
            $fs->remove($dir);
        }
        $this->em->remove($trick);
        $this->em->flush();
        $this->addFlash('success', 'The trick has been successfully deleted');
        return $this->redirectToRoute('home');
    }
    
    private function convertYoutube($string)
    {
        return preg_replace(
            "/\s*[a-zA-Z\/\/:\.]*youtu(be.com\/watch\?v=|.be\/)([a-zA-Z0-9\-_]+)([a-zA-Z0-9\/\*\-\_\?\&\;\%\=\.]*)/i",
            '<iframe frameborder="0" src="//www.youtube.com/embed/$2" allow="accelerometer;autoplay;clipboard-write-media;gyroscope;picture-in-picture" allowfullscreen></iframe>',
            $string
        );
    }
    
    private function addImages(array $images, Trick $trick)
    {
        // creating a new directory of uploads for each trick
        $fs = new Filesystem();
        $dir = $this->getParameter('tricks_directory') . '/' . $trick->getSlug();
        $fs->mkdir($dir);
        foreach ($images as $image) {
            $originalFileName = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $this->slugger->slug($originalFileName);
            $newFileName = $safeFilename . '-' . uniqid() . '.' . $image->guessExtension();
            $image->move(
                $dir,
                $newFileName
            );
            $image = new Image();
            $image->setName($newFileName);
            $image->setCreatedAt(new \DateTime());
            $image->setTrick($trick);
            $trick->addImage($image);
            $this->em->persist($image);
        }
    }
    
    private function addVideos(array $videos, Trick $trick)
    {
        foreach ($videos as $video) {
            if ($video) {
                $video->setCreatedAt(new \DateTime());
                $video->setEmbed($this->convertYoutube($video->getUrl()));
                $video->setTrick($trick);
                $trick->addVideo($video);
                $this->em->persist($video);
            }
        }
    }
}