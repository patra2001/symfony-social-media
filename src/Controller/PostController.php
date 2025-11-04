<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Like;
use App\Entity\Post;
use App\Entity\User;
use App\Form\CommentType;
use App\Form\PostType;
use App\Repository\CommentRepository;
use App\Repository\LikeRepository;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;
use App\Constant\PostConstant;

#[Route('/post')]
class PostController extends AbstractController
{
    #[Route('/create', name: 'app_post_create', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function create(
        Request $request,
        EntityManagerInterface $em,
        SluggerInterface $slugger
    ): Response {
        try{
        $post = new Post();
        
        //additional data
        $predefiendData = PostConstant::PostConstant();
        $predefinedFormData = is_array($predefiendData) ? reset($predefiendData) : [];
        if (method_exists($post, 'setExtraData')) {
            $post->setExtraData(json_encode($predefinedFormData));
        }
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $post->setAuthor($this->getUser());
            // Handle image upload
            $imageFile = $form->get('image')->getData();
            if ($imageFile instanceof UploadedFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('posts_directory'),
                        $newFilename
                    );
                    $post->setImage($newFilename);
                } catch (\Exception $e) {
                    $this->addFlash('danger', 'Failed to upload image: ' . $e->getMessage());
                    return $this->redirectToRoute('app_dashboard');
                }
            }

            $em->persist($post);
            $em->flush();

            $this->addFlash('success', 'Post created successfully!');
            return $this->redirectToRoute('app_post_feed');
        }

        return $this->render('post/create.html.twig', [
            'form' => $form->createView(),
            'extra_fields' => $predefiendData,
        ]);
         } catch (\Exception $e) {
            $this->addFlash('danger', $e->getMessage());
            return $this->redirectToRoute('app_post_feed');
        }
    }

    #[Route('/feed', name: 'app_post_feed', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function feed(
        Request $request,
        PostRepository $postRepository
    ): Response {
        try{
        $page = $request->query->getInt('page', 1);
        $postsPerPage = 10;

        $posts = $postRepository->findRecentPosts($page, $postsPerPage);
        $totalPosts = $postRepository->count([]);
        $totalPages = ceil($totalPosts / $postsPerPage);

        return $this->render('post/feed.html.twig', [
            'posts' => $posts,
            'currentPage' => $page,
            'totalPages' => $totalPages,
        ]);
         } catch (\Exception $e) {
            $this->addFlash('danger', $e->getMessage());
        }
    }

    #[Route('/{id}/view', name: 'app_post_view', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function view(
        Post $post,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        try{
        $comment = new Comment();
        $commentForm = $this->createForm(CommentType::class, $comment);
        $commentForm->handleRequest($request);

        if ($commentForm->isSubmitted() && $commentForm->isValid()) {
            $comment->setAuthor($this->getUser());
            $comment->setPost($post);

            $em->persist($comment);
            $em->flush();

            $this->addFlash('success', 'Comment added successfully!');
            return $this->redirectToRoute('app_post_view', ['id' => $post->getId()]);
        }

        return $this->render('post/view.html.twig', [
            'post' => $post,
            'commentForm' => $commentForm->createView(),
        ]);
         } catch (\Exception $e) {
            $this->addFlash('danger', $e->getMessage());
        }
    }

    #[Route('/{id}/edit', name: 'app_post_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function edit(
        Post $post,
        Request $request,
        EntityManagerInterface $em,
        SluggerInterface $slugger
    ): Response {
        try{
        $this->denyAccessUnlessGranted('edit', $post);

        // Build default extraData from PostConstant if not set
        $predefiendData = PostConstant::PostConstant();
        $predefinedFormData = is_array($predefiendData) ? reset($predefiendData) : [];
        $defaultEmbed = [];
        if (is_array($predefinedFormData)) {
            foreach ($predefinedFormData as $fieldName => $defaultValue) {
                $key = str_replace('-', '_', $fieldName);
                $defaultEmbed[$key] = $defaultValue;
            }
        }
        if (!$post->getExtraData() && method_exists($post, 'setExtraData')) {
            $post->setExtraData(json_encode($defaultEmbed));
        }

        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Handle image upload
            $imageFile = $form->get('image')->getData();
            if ($imageFile instanceof UploadedFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('posts_directory'),
                        $newFilename
                    );
                    $post->setImage($newFilename);
                } catch (\Exception $e) {
                    $this->addFlash('danger', 'Failed to upload image: ' . $e->getMessage());
                    return $this->redirectToRoute('app_post_view', ['id' => $post->getId()]);
                }
            }

            $post->setUpdatedAt(new \DateTimeImmutable());
            $em->flush();

            $this->addFlash('success', 'Post updated successfully!');
            return $this->redirectToRoute('app_post_view', ['id' => $post->getId()]);
        }

        return $this->render('post/edit.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
            'extra_fields' => $predefiendData,
        ]);
         } catch (\Exception $e) {
            $this->addFlash('danger', $e->getMessage());
        }
    }

    #[Route('/{id}/delete', name: 'app_post_delete', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function delete(
        Post $post,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        try{
        $this->denyAccessUnlessGranted('delete', $post);

        if ($this->isCsrfTokenValid('delete' . $post->getId(), $request->request->get('_token'))) {
            $em->remove($post);
            $em->flush();
            $this->addFlash('success', 'Post deleted successfully!');
        }

        return $this->redirectToRoute('app_post_feed');
         } catch (\Exception $e) {
            $this->addFlash('danger', $e->getMessage());
        }
    }

    #[Route('/{id}/like', name: 'app_post_like', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function like(
        Post $post,
        Request $request,
        EntityManagerInterface $em,
        LikeRepository $likeRepository
    ): Response {
        try{
        if ($this->isCsrfTokenValid('like' . $post->getId(), $request->request->get('_token'))) {
            $existingLike = $likeRepository->findOneBy([
                'post' => $post,
                'user' => $this->getUser(),
            ]);

            if ($existingLike) {
                $em->remove($existingLike);
                $this->addFlash('info', 'Like removed!');
            } else {
                $like = new Like();
                $like->setPost($post);
                $like->setUser($this->getUser());
                $em->persist($like);
                $this->addFlash('success', 'Post liked!');
            }

            $em->flush();
        }

        return $this->redirectToRoute('app_post_view', ['id' => $post->getId()]);
         } catch (\Exception $e) {
            $this->addFlash('danger', $e->getMessage());
        }
    }

    #[Route('/calendar/events', name: 'app_post_calendar_events', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function calendarEvents(
        PostRepository $postRepository,
        UserRepository $userRepository,
        Request $request
    ): Response {
        try {
            $currentUser = $this->getUser();
            if (!$currentUser instanceof User) {
                return $this->json(['error' => 'User not authenticated'], Response::HTTP_UNAUTHORIZED);
            }

            $authorId = $request->query->getInt('author', 0);
            $author = $authorId > 0 ? $userRepository->find($authorId) : $currentUser;

            if (!$author instanceof User) {
                return $this->json(['error' => 'Author not found'], Response::HTTP_NOT_FOUND);
            }

            $filter = strtolower((string) $request->query->get('filter', 'created'));
            if (!\in_array($filter, ['created', 'updated'], true)) {
                $filter = 'created';
            }

            $orderBy = $filter === 'updated' ? ['updatedAt' => 'DESC'] : ['createdAt' => 'DESC'];
            $posts = $postRepository->findBy(['author' => $author], $orderBy);

            $events = array_map(static function (Post $post) use ($filter) {
                $isUpdated = $post->getUpdatedAt() > $post->getCreatedAt();
                $rawTitle = trim((string) $post->getContent());

                if ($rawTitle === '') {
                    $rawTitle = sprintf('Post #%d', $post->getId());
                }

                $title = \mb_strlen($rawTitle) > 40 ? \mb_substr($rawTitle, 0, 37) . '…' : $rawTitle;

                return [
                    'id' => $post->getId(),
                    'title' => $title,
                    'start' => ($filter === 'updated' && $isUpdated)
                        ? $post->getUpdatedAt()->format(DATE_ATOM)
                        : $post->getCreatedAt()->format(DATE_ATOM),
                    'url' => sprintf('/post/%d/view', $post->getId()),
                    'backgroundColor' => $isUpdated ? '#f0ad4e' : '#198754',
                    'borderColor' => $isUpdated ? '#f0ad4e' : '#198754',
                    'classNames' => ['rounded-event'],
                    'extendedProps' => [
                        'createdAt' => $post->getCreatedAt()->format(DATE_ATOM),
                        'updatedAt' => $post->getUpdatedAt()->format(DATE_ATOM),
                        'isUpdated' => $isUpdated,
                    ],
                ];
            }, $posts);

            return $this->json($events);
        } catch (\Exception $exception) {
            return $this->json(['error' => 'Unable to load calendar events'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/comment/{id}/delete', name: 'app_comment_delete', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function deleteComment(
        Comment $comment,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        try{
        $this->denyAccessUnlessGranted('delete', $comment);
        $postId = $comment->getPost()->getId();

        if ($this->isCsrfTokenValid('delete-comment' . $comment->getId(), $request->request->get('_token'))) {
            $em->remove($comment);
            $em->flush();
            $this->addFlash('success', 'Comment deleted successfully!');
        }

        return $this->redirectToRoute('app_post_view', ['id' => $postId]);
         } catch (\Exception $e) {
            $this->addFlash('danger', $e->getMessage());
        }
    }
}