<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class EmailVerificationService
{
    private const VERIFICATION_TOKEN_LENGTH = 32;
    private const VERIFICATION_EXPIRY_HOURS = 24;

    public function __construct(
        private MailerInterface $mailer,
        private UrlGeneratorInterface $urlGenerator,
        private UserRepository $userRepository,
        private string $appName = 'SocialMedia',
    ) {
    }

    /**
     * Generate a unique verification token
     */
    public function generateToken(): string
    {
        return bin2hex(random_bytes(self::VERIFICATION_TOKEN_LENGTH / 2));
    }

    /**
     * Send verification email to user
     */
    public function sendVerificationEmail(User $user): void
    {
        // Generate token
        $token = $this->generateToken();
        $user->setEmailVerificationToken($token);
        $user->setEmailVerificationSentAt(new \DateTimeImmutable());

        // Generate verification link
        $verificationLink = $this->urlGenerator->generate(
            'app_verify_email',
            ['token' => $token],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        // Create and send email
        $email = (new TemplatedEmail())
            ->from(new Address('noreply@socialmedia.app', $this->appName))
            ->to(new Address($user->getEmail(), $user->getFullName()))
            ->subject('Verify Your Email Address')
            ->htmlTemplate('email/verify_email.html.twig')
            ->context([
                'user' => $user,
                'verificationLink' => $verificationLink,
                'expiryHours' => self::VERIFICATION_EXPIRY_HOURS,
                'appName' => $this->appName,
            ])
        ;

        $this->mailer->send($email);

        // Save token to user
        $this->userRepository->save($user, true);
    }

    /**
     * Resend verification email
     */
    public function resendVerificationEmail(User $user): void
    {
        if ($user->isEmailVerified()) {
            throw new \LogicException('Email is already verified');
        }

        $this->sendVerificationEmail($user);
    }

    /**
     * Verify email token and mark user as verified
     */
    public function verifyEmail(string $token): User
    {
        $user = $this->userRepository->findByEmailVerificationToken($token);

        if (!$user) {
            throw new \InvalidArgumentException('Invalid verification token');
        }

        // Check if token has expired (24 hours)
        $sentAt = $user->getEmailVerificationSentAt();
        $now = new \DateTimeImmutable();
        $interval = $now->getTimestamp() - $sentAt->getTimestamp();
        $expirySeconds = self::VERIFICATION_EXPIRY_HOURS * 3600;

        if ($interval > $expirySeconds) {
            throw new \InvalidArgumentException('Verification token has expired');
        }

        $user->setEmailVerified(true);
        $user->setEmailVerificationToken(null);
        $user->setEmailVerificationSentAt(null);

        $this->userRepository->save($user, true);

        return $user;
    }

    /**
     * Check if token is valid and not expired
     */
    public function isTokenValid(string $token): bool
    {
        $user = $this->userRepository->findByEmailVerificationToken($token);

        if (!$user) {
            return false;
        }

        $sentAt = $user->getEmailVerificationSentAt();
        if (!$sentAt) {
            return false;
        }

        $now = new \DateTimeImmutable();
        $interval = $now->getTimestamp() - $sentAt->getTimestamp();
        $expirySeconds = self::VERIFICATION_EXPIRY_HOURS * 3600;

        return $interval <= $expirySeconds;
    }
}