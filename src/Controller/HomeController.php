<?php

namespace App\Controller;

use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        return $this->render('home/index.html.twig', [
            'user' => $this->getUser(),
        ]);
    }

    #[Route('/dashboard', name: 'app_dashboard')]
    public function dashboard(Request $request, PostRepository $postRepository): Response
    {
        try{
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED');

        $page = $request->query->getInt('page', 1);
        $postsPerPage = 10;

        $posts = $postRepository->findRecentPosts($page, $postsPerPage);
        $totalPosts = $postRepository->count([]);
        $totalPages = ceil($totalPosts / $postsPerPage);

        return $this->render('dashboard/index.html.twig', [
            'user' => $this->getUser(),
            'posts' => $posts,
            'currentPage' => $page,
            'totalPages' => $totalPages,
        ]);
         } catch (\InvalidArgumentException $e) {
            $this->addFlash('danger', $e->getMessage());
        }
    }
}