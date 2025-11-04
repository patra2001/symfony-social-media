<?php

namespace App\Controller;

use App\Service\AlbumsApiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
// use Symfony\Contracts\HttpClient\HttpClientInterface; //for api call

class AlbumsController extends AbstractController
{
    private AlbumsApiService $albumsApiService;

    public function __construct(AlbumsApiService $albumsApiService)
    {
        $this->httpClient = $albumsApiService;
    }

    //list
    #[Route('/albums/list', name: 'albums_index')]
    public function index(): Response
    {
        $albums = $this->httpClient->getAlbums();
        return $this->render('albums/index.html.twig', [
            'albums' => $albums,
        ]);
    }

    //show
    #[Route('/albums/{id}', name: 'albums_show', requirements: ['id' => '\\d+'])]
    public function show(int $id): Response
    {
        $post = $this->httpClient->getPost($id);
        return $this->render('albums/show.html.twig', [
            'id' => $id,
            'post' => $post,
        ]);
    }

    //comments
    // #[Route('/albums/{id}/comments', name: 'albums_comments', requirements: ['id' => '\\d+'])]
    // public function comments(int $id): Response
    // {
    //     $response = $this->httpClient->request('GET', 'https://jsonplaceholder.typicode.com/posts/' . $id . '/comments');
    //     $comments = $response->toArray();

    //     return $this->render('albums/comments.html.twig', [
    //         'id' => $id,
    //         'comments' => $comments,
    //     ]);
    // }

    
}
