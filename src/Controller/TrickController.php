<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Image;
use App\Entity\Trick;
use App\Entity\User;
use App\Form\CommentType;
use App\Form\TrickFormType;
use App\Repository\CommentRepository;
use App\Repository\ImageRepository;
use App\Repository\TrickRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

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
    /**
     * @var TrickRepository
     */
    private $trickRepository;
    /**
     * @var ImageRepository
     */
    private $imageRepository;
    /**
     * @var CommentRepository
     */
    private $commentRepository;
    
    public function __construct(
        SluggerInterface $slugger,
        EntityManagerInterface $em,
        TrickRepository $trickRepository,
        ImageRepository $imageRepository,
        CommentRepository $commentRepository
    ) {
        $this->slugger = $slugger;
        $this->em = $em;
        $this->trickRepository = $trickRepository;
        $this->imageRepository = $imageRepository;
        $this->commentRepository = $commentRepository;
    }
    
    /**
     * @Route("/tricks/get/{lastId}", name="tricks_load_more")
     * @param int $lastId
     * @param SerializerInterface $serializer
     * @return Response
     */
    public function loadTricks(int $lastId, SerializerInterface $serializer): Response
    {
        $tricks = $this->trickRepository->loadMoreTricks($lastId);
        /* $encoder = new JsonEncoder();
         $defaultContext = [
             AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object, $format, $context) {
                 return $object->getName();
             },
         ];
         $normalizer = new ObjectNormalizer(null, null, null, null, null, null, $defaultContext);

         $serializer = new Serializer([$normalizer], [$encoder]);
         $serializedTricks = $serializer->normalize($tricks, null, array('enable_max_depth' => true));
         dump($serializedTricks);
         exit;*/
        $response = new Response();
        $response->setContent(
            $serializer->serialize(
                $tricks,
                'json',
                ['groups' => ['trick']]
            )
        );
        $response->headers->set('Content-Type', 'application/json');
        
        return $response;
    }
    
    /**
     * @Route("/trick/{slug}", name="trick_show")
     * @param string $slug
     * @param Request $request
     * @return Response
     */
    public function showTrick(string $slug, Request $request): Response
    {
        $trick = $this->trickRepository->findOneBy(['slug' => $slug]);
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
            return $this->forward(
                'App\Controller\CommentController::add',
                [
                    'commentForm' => $commentForm,
                    'trick' => $trick,
                    'comment' => $comment,
                    'user' => $user,
                ]
            );
        }
        $comments = $this->commentRepository->getComments($trick->getId());
        $commentsCount = $this->commentRepository->countComments($trick->getId());
        
        return $this->render(
            'trick/show.html.twig',
            [
                'trick' => $trick,
                'commentForm' => $commentForm->createView(),
                'comments' => $comments,
                'commentsCount' => $commentsCount,
            ]
        );
    }
    
    /**
     * @Route("/trick/add", name="trick_add", priority=2)
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
            $trick->setName(ucfirst(mb_strtolower($form->get('name')->getData(), 'UTF-8')));
            $trick->setSlug($this->slugger->slug(mb_strtolower($trick->getName(), 'UTF-8')));
            $trick->setCreatedAt(new \DateTime());
            $trick->setUser($user);
            // get images from $form and uploads them into their trick's directory
            
            $images = $form->get('images')->getData();
            if ($images) {
                // traitement directory
                $fs = new Filesystem();
                $dir = $this->getParameter('tricks_directory').'/'.$trick->getSlug();
                $fs->mkdir($dir);
                foreach ($images as $key => $image) {
                    $this->addImage($image, $key, $trick, $dir);
                }
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
        
        return $this->render('trick/add.html.twig', ['form' => $form->createView()]);
    }
    
    /**
     * @Route("/trick/{slug}/edit", name="trick_edit")
     * @param string $slug
     * @param Request $request
     * @return Response
     */
    public function editTrick(string $slug, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $trick = $this->trickRepository->findOneBy(['slug' => $slug]);
        $form = $this->createForm(TrickFormType::class, $trick);
        $oldTrickName = $trick->getName();
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            // rename trick's image folder if trick's name has changed
            if ($form->get('name')->getData() !== $oldTrickName) {
                $fs = new Filesystem();
                $oldDir = $this->getParameter('tricks_directory').'/'.$trick->getSlug();
                $trick->setSlug($this->slugger->slug(mb_strtolower($trick->getName(), 'UTF-8')));
                if ($fs->exists($oldDir)) {
                    $newDir = $this->getParameter('tricks_directory').'/'.$trick->getSlug();
                    $fs->rename($oldDir, $newDir);
                }
            }
            $images = $form->get('images')->getData();
            if ($images) {
                $fs = new Filesystem();
                $dir = $this->getParameter('tricks_directory').'/'.$trick->getSlug();
                $fs->mkdir($dir);
                foreach ($images as $key => $image) {
                    $this->addImage($image, $key, $trick, $dir);
                }
            } elseif (isset($_POST['mainPictureRadio'])) {
                // in this loop if no image is added
                $radioId = filter_var($_POST['mainPictureRadio'], FILTER_SANITIZE_NUMBER_INT);
                if ($radioId !== $this->imageRepository->findMainPic($trick->getId())) {
                    $image = $this->imageRepository->find($radioId);
                    if (isset($this->imageRepository->findMainPic($trick->getId())[0])) {
                        $this->imageRepository->findMainPic($trick->getId())[0]->setIsMain(false);
                    }
                    $image->setIsMain(true);
                }
            } elseif (!$trick->getImages()) {
                $image = $this->trickRepository->find($trick->getId())->getImages()->first();
                $image->setIsMain(true);
            }
            
            $videosUrl = $form->get('videos')->getData();
            if ($videosUrl) {
                $this->addVideos($videosUrl, $trick);
            }
            $trick->setName(ucfirst(mb_strtolower($form->get('name')->getData(), 'UTF-8')));
            $this->em->persist($trick);
            $this->em->flush();
            $this->addFlash('success', 'The trick has been successfully updated');
            
            return $this->redirectToRoute('trick_show', ['slug' => $trick->getSlug()]);
        }
        
        return $this->render(
            'trick/edit.html.twig',
            [
                'form' => $form->createView(),
                'trick' => $trick,
            ]
        );
    }
    
    /**
     * @Route("/trick/{slug}/delete", name="trick_delete")
     * @param string $slug
     * @return RedirectResponse
     */
    public function deleteTrick(string $slug): RedirectResponse
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $trick = $this->trickRepository->findOneBy(['slug' => $slug]);
        if (!$trick) {
            $this->addFlash('danger', 'No trick can be found');
            
            return $this->redirectToRoute('home');
        }
        $fs = new Filesystem();
        $dir = $this->getParameter('tricks_directory').'/'.$trick->getSlug();
        if ($fs->exists($dir)) {
            $fs->remove($dir);
        }
        $this->em->remove($trick);
        $this->em->flush();
        $this->addFlash('success', 'The trick has been successfully deleted');
        
        return $this->redirectToRoute('home');
    }
    
    /**
     * @Route("/ajax/removeImages", name="ajax_remove", methods="{POST}")
     * @param Request $request
     * @return JsonResponse
     */
    public function ajaxRemoveImage(Request $request): JsonResponse
    {
        return new JsonResponse($request->request->get('request'));
    }
    
    private function convertYoutube($string)
    {
        return preg_replace(
            "/\s*[a-zA-Z\/\/:\.]*youtu(be.com\/watch\?v=|.be\/)([a-zA-Z0-9\-_]+)([a-zA-Z0-9\/\*\-\_\?\&\;\%\=\.]*)/i",
            '<iframe frameborder="0" src="//www.youtube.com/embed/$2" allow="accelerometer;autoplay;clipboard-write-media;gyroscope;picture-in-picture" allowfullscreen></iframe>',
            $string
        );
    }
    
    private function addImage($image, $key, Trick $trick, $dir)
    {
        $originalFileName = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFileName);
        $newFileName = $safeFilename.'-'.uniqid().'.'.$image->guessExtension();
        $image->move(
            $dir,
            $newFileName
        );
        $image = new Image();
        $image->setName($newFileName);
        $image->setCreatedAt(new \DateTime());
        if ($key == $_POST['mainPictureRadio']) {
            if (isset($this->imageRepository->findMainPic($trick->getId())[0])) {
                $actualMainPic = $this->imageRepository->findMainPic($trick->getId())[0];
                if ($actualMainPic->getName() !== $image->getName()) {
                    $actualMainPic->setIsMain(false);
                }
            }
            $image->setIsMain(true);
        }
        $image->setTrick($trick);
        $trick->addImage($image);
        $this->em->persist($image);
    }
    
    private function addVideos(array $videos, Trick $trick)
    {
        if ($videos) {
            foreach ($videos as $video) {
                if (isset($video)) {
                    $video->setCreatedAt(new \DateTime());
                    $video->setEmbed($this->convertYoutube($video->getUrl()));
                    $video->setTrick($trick);
                    parse_str(parse_url($video->getUrl(), PHP_URL_QUERY), $array);
                    $video->setYoutubeId($array['v']);
                    $trick->addVideo($video);
                    $this->em->persist($video);
                }
            }
        }
    }
}
