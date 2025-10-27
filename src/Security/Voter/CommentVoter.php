<?php

namespace App\Security\Voter;

use App\Entity\Comment;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class CommentVoter extends Voter
{
    public const DELETE = 'delete';
    public const EDIT = 'edit';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::DELETE, self::EDIT])
            && $subject instanceof Comment;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        /** @var Comment $comment */
        $comment = $subject;

        return match ($attribute) {
            self::DELETE => $this->canDelete($comment, $user),
            self::EDIT => $this->canEdit($comment, $user),
            default => false,
        };
    }

    private function canDelete(Comment $comment, User $user): bool
    {
        // Only the comment author can delete
        return $comment->getAuthor() === $user;
    }

    private function canEdit(Comment $comment, User $user): bool
    {
        // Only the comment author can edit
        return $comment->getAuthor() === $user;
    }
}