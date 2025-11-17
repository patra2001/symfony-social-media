<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserProfileType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Constant\PostConstant;


#[Route('/profile')]
class UserProfileController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SluggerInterface $slugger,
    ) {}

    #[Route('/{id}', name: 'app_profile', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function view(UserRepository $userRepository, int $id, Request $request,): Response
    { 
        $routeName = $request->attributes->get('_route');
        try {
        $user = $userRepository->find($id);
        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }
        // ['clients_extrafield'];
        // dd($predefiendData);die;
        $clientsextrafields = PostConstant::PostConstant($routeName);
        // $clientsextrafields = $constants['clients_extrafield'] ?? [];
        // dd($clientsextrafields);die;


        $storedextrafield = $user->getClientsMissionData();
        $defaultextrafield = [];

        if ($storedextrafield) {
            $decoded = json_decode($storedextrafield, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $defaultextrafield = $decoded;
            }
        }

        if (empty($defaultextrafield) && !empty($clientsextrafields)) {
            $defaultextrafield = $clientsextrafields[0];
        }

        // Get user's posts ordered by creation date
        $posts = $user->getPosts();
        
        return $this->render('user/profile/view.html.twig', [
            'profile_user' => $user,
            'posts' => $posts,
            'clientsextrafields' => $clientsextrafields,
            'defaultextrafield' => $defaultextrafield,
        ]);
         } catch (\InvalidArgumentException $e) {
            $this->addFlash('danger', $e->getMessage());
        }
    }

    #[Route('/edit', name: 'app_profile_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function edit(Request $request,
    UserPasswordHasherInterface $passwordHasher
    ): Response
    {
        try{
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('You must be logged in');
        }

        $form = $this->createForm(UserProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Handle profile image upload
            /** @var UploadedFile $profileImageFile */
            $profileImageFile = $form->get('profileImage')->getData();

            $plainPassword = $form->get('password')->getData();

            if ($profileImageFile) {
                $originalFilename = pathinfo($profileImageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $this->slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $profileImageFile->guessExtension();

                try {
                    $profilesDirectory = $this->getParameter('profiles_directory');
                    $profileImageFile->move($profilesDirectory, $newFilename);
                    $user->setProfileImage($newFilename);
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Failed to upload profile image: ' . $e->getMessage());
                }
            }

            // Update timestamp
            $user->setUpdatedAt(new \DateTimeImmutable());

            if (!empty($plainPassword)) {
                $encodedPassword = $passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($encodedPassword);
            }

            $this->entityManager->flush();
            $this->addFlash('success', 'Profile updated successfully!');

            return $this->redirectToRoute('app_profile', ['id' => $user->getId()]);
        }

        return $this->render('user/profile/edit.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
         } catch (\Exception $e) {
           $errorMessage = sprintf(
        "Error: %s in %s on line %d",
        $e->getMessage(),
        $e->getFile(),
        $e->getLine()
    );

    $this->addFlash('danger', $errorMessage);
    return $this->redirectToRoute('app_profile_edit');
        }

    }

    #[Route('/{id}/follow', name: 'app_profile_follow', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function follow(UserRepository $userRepository, int $id, Request $request): Response
    {
        try{
        $userToFollow = $userRepository->find($id);
        if (!$userToFollow) {
            throw $this->createNotFoundException('User not found');
        }

        $this->validateCsrfToken('follow', $request->request->get('_token'));

        $currentUser = $this->getUser();
        if (!$currentUser instanceof User) {
            throw $this->createAccessDeniedException('You must be logged in');
        }

        if ($currentUser === $userToFollow) {
            $this->addFlash('error', 'You cannot follow yourself');
            return $this->redirectToRoute('app_profile', ['id' => $userToFollow->getId()]);
        }

        if (!$currentUser->getFollowing()->contains($userToFollow)) {
            $currentUser->addFollowing($userToFollow);
            $this->entityManager->flush();
            $this->addFlash('success', 'You are now following ' . $userToFollow->getFullName());
        } else {
            $this->addFlash('info', 'You are already following this user');
        }

        return $this->redirectToRoute('app_profile', ['id' => $userToFollow->getId()]);
         } catch (\InvalidArgumentException $e) {
            $this->addFlash('danger', $e->getMessage());
        }
    }

    #[Route('/{id}/unfollow', name: 'app_profile_unfollow', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function unfollow(UserRepository $userRepository, int $id, Request $request): Response
    {
        try{
        $userToUnfollow = $userRepository->find($id);
        if (!$userToUnfollow) {
            throw $this->createNotFoundException('User not found');
        }

        $this->validateCsrfToken('unfollow', $request->request->get('_token'));

        $currentUser = $this->getUser();
        if (!$currentUser instanceof User) {
            throw $this->createAccessDeniedException('You must be logged in');
        }

        if ($currentUser->getFollowing()->contains($userToUnfollow)) {
            $currentUser->getFollowing()->removeElement($userToUnfollow);
            $userToUnfollow->removeFollower($currentUser);
            $this->entityManager->flush();
            $this->addFlash('success', 'You unfollowed ' . $userToUnfollow->getFullName());
        }

        return $this->redirectToRoute('app_profile', ['id' => $userToUnfollow->getId()]);
         } catch (\InvalidArgumentException $e) {
            $this->addFlash('danger', $e->getMessage());
        }
    }

    private function validateCsrfToken(string $intention, ?string $token): void
    {
        if (!$this->isCsrfTokenValid($intention, $token)) {
            throw $this->createAccessDeniedException('Invalid CSRF token');
        }
    }

    #[Route('/save-extrafield', name: 'app_save_extrafield', methods: ['POST'])]
    public function saveextrafield(Request $request, EntityManagerInterface $em): JsonResponse
    {
        try{
        $contentType = $request->headers->get('Content-Type', '');
        if ($contentType && str_starts_with($contentType, 'application/json')) {
            $extrafieldData = json_decode($request->getContent(), true);
        } else {
            $extrafieldData = json_decode($request->request->get('extrafield_data', ''), true);
        }
        if (!is_array($extrafieldData)) {
            return new JsonResponse(['error' => 'Invalid extrafield payload'], 400);
        }
        $user = $this->getUser();
        if (!$user instanceof User) {
            return new JsonResponse(['error' => 'Unauthorized'], 403);
        }
        $user->setClientsMissionData(json_encode($extrafieldData));
        $em->persist($user);
        $em->flush();

        return new JsonResponse(['status' => 'success']);
        } catch (\Excertion $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        }
    }


}