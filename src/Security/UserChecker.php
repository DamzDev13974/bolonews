<?php
//Fichier permettant de gerer les vérifications d'utilisateurs au moment du login(verif du ban)


namespace App\Security;

use App\Entity\User as AppUser;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        //Verifie si il y un user
        if (!$user instanceof AppUser) {
            return;
        }

        //Si l'user est banni
        if ($user->isBanni()) {
            // Message ttransmis et redirection géré dans  security.yaml
            throw new CustomUserMessageAccountStatusException('Votre compte a été banni.');
        }
    }

    public function checkPostAuth(UserInterface $user, ?TokenInterface $token = null): void
    {
        //A remplir si rajout de verif après l'authentification
    }
}