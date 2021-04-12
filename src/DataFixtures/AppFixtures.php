<?php

namespace App\DataFixtures;

use App\Entity\Trick;
use App\Entity\TrickGroup;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

class AppFixtures extends Fixture
{
    private $slugger;
    
    /**
     * @var UserPasswordEncoderInterface
     */
    private $encoder;
    
    public function __construct(SluggerInterface $slugger, UserPasswordEncoderInterface $encoder)
    {
        $this->slugger = $slugger;
        $this->encoder = $encoder;
    }
    
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $tricksArray = [
            1 => [
                "name" => "Regular",
                "group" => "Stances",
                "description" => "Rides with left foot forward in natural stance.",
            ],
            2 => [
                "name" => "Shifty",
                "group" => "Straight airs",
                "description" => "An aerial trick in which a snowboarder counter-rotates their upper body in order
                to shift their board about 90° from its normal position beneath them,
                and then returns the board to its original position before landing.
                This trick can be performed frontside or backside, and also in variation with other tricks and spins.",
            ],
            3 => [
                "name" => "Butter",
                "group" => "Miscellaneous tricks and identifiers",
                "description" => "While traveling along the surface of the snow, this trick is performed
                 by pressuring either the nose or tail of the snowboard in such a way
                 that the opposite half of the snowboard lifts off of the snow,
                  allowing for a pivot-like rotation. A butter can be performed as a partial
                  rotation (90°), which is then reverted, as a continuous rotation (180°, 360°, etc.),
                  or as a lead-in to an aerial maneuver. (butters are similar to blunt slides in skateboarding)",
            ],
            4 => [
                "name" => "One-footed",
                "group" => "Tweaks and variations",
                "description" => "Tricks performed with one foot removed from the binding (typically the rear foot)
                are referred to as one-footed tricks. One footed tricks include fast plants in which the rear foot is
                dropped and initiates a straight air or rotation, the boneless, which is a fast-plant with a grab;
                and the no-comply, which is a front-footed fast plant.",
            ],
            5 => [
                "name" => "Blunt-stall",
                "group" => "Stalls",
                "description" => "Mimicking skateboarding, and similar to a board-stall, this trick is performed
                by stalling on an object with the tail of the board (blunt stall), or the nose of
                the board (nose blunt stall). Distinguished from a nose-stall or tail-stall because during the stall,
                most of the snowboard will be positioned above the obstacle and point of contact.",
            ],
            6 => [
                "name" => "Boardslide",
                "group" => "Slides",
                "description" => "A slide performed where the riders leading foot passes over the rail on approach,
                with their snowboard traveling perpendicular along the rail or other obstacle.[1] When performing
                a frontside boardslide, the snowboarder is facing uphill. When performing a backside boardslide,
                a snowboarder is facing downhill. This is often confusing to new riders learning the
                trick because with a frontside boardslide you are moving backward and with a backside
                boardslide you are moving forward.",
            ],
            7 => [
                "name" => "Eggflip",
                "group" => "Inverted hand plants",
                "description" => "An eggplant where the rider chooses to flip over in order to re-enter
                the pipe instead or rotating 180 degrees. This trick is performed forward to fakie or
                switch (fakie to forward).",
            ],
            8 => [
                "name" => "Backside Misty",
                "group" => "Flips and inverted rotations",
                "description" => "After a rider learns the basic backside 540 off the toes, the Misty Flip can be an
                easy next progression step. Misty Flip is quite different than the backside rodeo, because instead
                of corking over the heel edge with a back flip motion, the Misty corks off the toe edge specifically
                and has more of a Front Flip in the beginning of the trick, followed by a side flip coming
                out to the landing.",
            ],
            9 => [
                "name" => "Alley-oop",
                "group" => "Spins",
                "description" => "A spin performed in a halfpipe or quarterpipe in which the spin is rotated in
                the opposite direction of the air. For example, performing a frontside rotation on the backside
                wall of a halfpipe, or spinning clockwise while traveling right-to-left through the air on a
                quarterpipe would mean the spin was alley-oop.",
            ],
            10 => [
                "name" => "Frontside grab/indy",
                "group" => "Grabs",
                "description" => "A fundamental trick performed by grabbing the toe edge between the bindings with
                the trailing hand. This trick is referred to as a frontside grab on a straight air, or
                while performing a frontside spin. When performing a backside aerial or backside rotation,
                this grab is referred to as an Indy. The frontside air was popularized by skateboarder Tony Alva..",
            ],
        
        ];
        $user = new User();
        $user->setEmail('snowtricks@example.com');
        $user->setFirstname('Jimmy');
        $user->setLastname('Sweat');
        $user->setIsVerified(true);
        $user->setPassword($this->encoder->encodePassword($user, 'SnowTricks123!*'));
        $user->setCreatedAt(new \DateTime());
        $manager->persist($user);
        foreach ($tricksArray as $trickArray) {
            $trick = new Trick();
            $trick->setName($trickArray['name']);
            $trick->setSlug($this->slugger->slug($trick->getName()));
            $trick->setDescription($trickArray['description']);
            $group = new TrickGroup();
            $group->setName($trickArray['group']);
            $trick->setTrickGroup($group);
            $trick->setCreatedAt(new \DateTime());
            $trick->setUser($user);
            $group->addTrick($trick);
            $manager->persist($trick);
            $manager->persist($group);
        }
        $manager->flush();
    }
}
