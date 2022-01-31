<?php

namespace App\Security\Voter;

use App\Entity\Team;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class TeamVoter extends Voter {
    protected function supports(string $attribute, $subject): bool {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, ['TEAM_MANAGE', 'TEAM_SEE'])
            && $subject instanceof Team;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool {
        /** @var Team $subject */
        /** @var User $user */
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case 'TEAM_MANAGE':
                return $subject->getManager()->getId() === $user->getId();
            case 'TEAM_SEE':
                return $user->getTeams()->contains($subject);
        }

        return false;
    }
}
