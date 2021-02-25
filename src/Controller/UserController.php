<?php


namespace App\Controller;

use App\Form\UserUpdateType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * Class UserController
 *
 * @author Nicolas Halberstadt <halberstadtnicolas@gmail.com>
 * @package App\Controller
 */
class UserController extends AbstractController
{
    /**
     * @var UserPasswordEncoderInterface
     */
    private $encoder;
    
    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }
    
    /**
     * @Route("/profile", name="user_profile")
     */
    public function profile(): Response
    {
        $user = $this->getUser();
        return $this->render('user/profile.html.twig', [
            'user' => $user
        ]);
    }
    
    /**
     * @Route("profile/update", name="user_profile_update")
     * @param Request $request
     * @param SluggerInterface $slugger
     * @return Response
     */
    public function updateProfile(Request $request, SluggerInterface $slugger): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(UserUpdateType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $typedPassword = $form->get('plainPassword')->getData();
            $compare = $this->encoder->isPasswordValid($user, $typedPassword);
            if (!$compare) {
                $this->addFlash('warning', 'Invalid credentials');
                return $this->redirectToRoute('user_profile_update');
            }
            $avatar = $form->get('avatar')->getData();
            if ($avatar) {
                $originalFileName = pathinfo($avatar->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFileName);
                $newFileName = $safeFilename . '-' . uniqid() . '.' . $avatar->guessExtension();
                try {
                    $avatar->move(
                        $this->getParameter('avatar_directory'),
                        $newFileName
                    );
                } catch (\Exception $e) {
                    echo $e->getMessage();
                    exit;
                }
                $avatarPath = $this->getParameter('avatar_directory') . '/' . $user->getAvatar();
                $fs = new Filesystem();
                $fs->remove($avatarPath);
                $user->setAvatar($newFileName);
            }
            $user->setUpdatedAt(new \DateTime());
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'Your profile has been updated');
            return $this->redirectToRoute('user_profile');
        }
        $avatar = null;
        if ($user->getAvatar() !== null) {
            $avatar = $user->getAvatar();
        }
        return $this->render('user/update.html.twig', [
            'user' => $user,
            'avatar' => $avatar,
            'form' => $form->createView()
        ]);
        
        // TODO: Add delete user method
    }
}