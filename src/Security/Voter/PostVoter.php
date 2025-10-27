<?php

namespace App\Security\Voter;

use App\Entity\Post;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class PostVoter extends Voter
{
    public const EDIT = 'edit';
    public const DELETE = 'delete';
    public const VIEW = 'view';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::DELETE, self::VIEW])
            && $subject instanceof Post;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        /** @var Post $post */
        $post = $subject;

        return match ($attribute) {
            self::EDIT => $this->canEdit($post, $user),
            self::DELETE => $this->canDelete($post, $user),
            self::VIEW => $this->canView($post, $user),
            default => false,
        };
    }

    private function canEdit(Post $post, User $user): bool
    {
        // Only the post author can edit
        return $post->getAuthor() === $user;
    }

    private function canDelete(Post $post, User $user): bool
    {
        // Only the post author can delete
        return $post->getAuthor() === $user;
    }

    private function canView(Post $post, User $user): bool
    {
        // Any authenticated user can view posts
        return true;
    }
}