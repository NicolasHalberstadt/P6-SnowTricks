<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class HomeController
 *
 * @author Nicolas Halberstadt <halberstadtnicolas@gmail.com>
 * @package App\Controller
 */
class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function home(): Response
    {
        $user = $this->getUser();
        return $this->render('home.html.twig', [
            'user' => $user
        ]);
    }
}