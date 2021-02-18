<?php


namespace App\Security;

use Symfony\Component\Security\Core\Exception\AccountStatusException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class UserChecker
 *
 * @author Nicolas Halberstadt <halberstadtnicolas@gmail.com>
 * @package App\Security
 */
class UserChecker implements UserCheckerInterface
{
    
    public function checkPreAuth(UserInterface $user)
    {
        if (!$user->isVerified()) {
            throw new CustomUserMessageAuthenticationException('You need to verify your email address first');
        }
    }
    
    public function checkPostAuth(UserInterface $user)
    {
        // TODO: Implement checkPostAuth() method.
    }
}