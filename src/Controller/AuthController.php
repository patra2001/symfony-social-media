<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Service\EmailVerificationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

#[Route('/auth')]
class AuthController extends AbstractController
{
    #[Route('/register', name: 'app_register', methods: ['GET', 'POST'])]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        UserRepository $userRepository,
        EmailVerificationService $emailVerificationService,
    ): Response {
        // Redirect if already logged in
        try {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Check if email already exists
            $existingUser = $userRepository->findOneBy(['email' => $user->getEmail()]);
            if ($existingUser) {
                $this->addFlash('danger', 'This email is already registered');
                return $this->redirectToRoute('app_register');
            }

            // Hash password
            $plainPassword = $form->get('plainPassword')->getData();
            $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
            $user->setPassword($hashedPassword);

            // Save user
            $userRepository->save($user, true);

            // Send verification email
            try {
                $emailVerificationService->sendVerificationEmail($user);
                $this->addFlash('success', 'Registration successful! Please check your email to verify your account.');
                return $this->redirectToRoute('app_login');
            } catch (\Exception $e) {
                $this->addFlash('danger', 'Failed to send verification email. Please try again later.');
                return $this->redirectToRoute('app_register');
            }
        }

        return $this->render('auth/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
         } catch (\InvalidArgumentException $e) {
            $this->addFlash('danger', $e->getMessage());
        }
    }

    #[Route('/login', name: 'app_login', methods: ['GET', 'POST'])]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Redirect if already logged in
        try {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        // Get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
// dump($error);die;
        // Last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('auth/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
         } catch (\InvalidArgumentException $e) {
            $this->addFlash('danger', $e->getMessage());
        }
    }

    #[Route('/verify-email/{token}', name: 'app_verify_email', methods: ['GET'])]
    public function verifyEmail(
        string $token,
        EmailVerificationService $emailVerificationService,
        Security $security,
    ): Response {
        try {
            $user = $emailVerificationService->verifyEmail($token);
            $this->addFlash('success', 'Email verified successfully! You can now log in.');

            // Auto-login user
            $security->login($user, 'form_login');

            return $this->redirectToRoute('app_home');
        } catch (\InvalidArgumentException $e) {
            $this->addFlash('danger', $e->getMessage());
            return $this->redirectToRoute('app_login');
        }
    }

    #[Route('/resend-verification', name: 'app_resend_verification', methods: ['POST'])]
    public function resendVerification(
        Request $request,
        UserRepository $userRepository,
        EmailVerificationService $emailVerificationService,
    ): Response {
        try{
        $email = $request->request->get('email');

        if (!$email) {
            $this->addFlash('danger', 'Email address is required');
            return $this->redirectToRoute('app_login');
        }

        $user = $userRepository->findOneBy(['email' => $email]);

        if (!$user) {
            // Don't reveal if email exists
            $this->addFlash('info', 'If the email exists and is unverified, a verification link will be sent.');
            return $this->redirectToRoute('app_login');
        }

        if ($user->isEmailVerified()) {
            $this->addFlash('info', 'This email is already verified');
            return $this->redirectToRoute('app_login');
        }

        try {
            $emailVerificationService->resendVerificationEmail($user);
            $this->addFlash('success', 'Verification email sent! Please check your inbox.');
        } catch (\Exception $e) {
            $this->addFlash('danger', 'Failed to send verification email. Please try again later.');
        }

        return $this->redirectToRoute('app_login');
         } catch (\InvalidArgumentException $e) {
            $this->addFlash('danger', $e->getMessage());
        }
    }

    #[Route('/logout', name: 'app_logout', methods: ['POST'])]
    public function logout(): never
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}