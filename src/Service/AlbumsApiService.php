<?php

namespace App\Service;
use Symfony\Contracts\HttpClient\HttpClientInterface;
 
class AlbumsApiService
{
    private HttpClientInterface $httpClient;
    private string $baseUrl = 'https://jsonplaceholder.typicode.com';

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

     public function getAlbums(): array
    {
        $response = $this->httpClient->request('GET', $this->baseUrl . '/albums');
        return $response->toArray();
    }

    public function getPost(int $id): array
    {
        $response = $this->httpClient->request('GET', $this->baseUrl . '/posts/' . $id);
        return $response->toArray();
    }
}