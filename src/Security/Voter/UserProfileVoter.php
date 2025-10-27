<?php

namespace App\Security\Voter;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class UserProfileVoter extends Voter
{
    public const EDIT = 'edit';
    public const VIEW = 'view';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::VIEW])
            && $subject instanceof User;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        /** @var User $profile */
        $profile = $subject;

        return match ($attribute) {
            self::VIEW => $this->canView($profile, $user),
            self::EDIT => $this->canEdit($profile, $user),
            default => false,
        };
    }

    private function canView(User $profile, User $user): bool
    {
        // Any authenticated user can view any profile
        return true;
    }

    private function canEdit(User $profile, User $user): bool
    {
        // Only the profile owner can edit their profile
        return $profile->getId() === $user->getId();
    }
}