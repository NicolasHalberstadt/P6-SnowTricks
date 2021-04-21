<?php

namespace App\Controller;

use App\Repository\TrickRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class SiteController
 *
 * @author Nicolas Halberstadt <halberstadtnicolas@gmail.com>
 * @package App\Controller
 */
class SiteController extends AbstractController
{
    /**
     * @Route("/", name="home")
     * @param TrickRepository $trickRepository
     * @return Response
     */
    public function home(TrickRepository $trickRepository): Response
    {
        $user = $this->getUser();
        $tricks = $trickRepository->findHomepageTricks();
        
        return $this->render(
            'home.html.twig',
            [
                'user' => $user,
                'tricks' => $tricks,
            ]
        );
    }
}