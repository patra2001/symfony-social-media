<?php

namespace App\Tests\Controller;

use App\Entity\Post;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class PostControllerTest extends WebTestCase
{
    private $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testCreatePostRequiresLogin(): void
    {
        $this->client->request('GET', '/post/create');
        $this->assertResponseRedirects('/login');
    }

    public function testCreatePostPageLoadsForAuthenticatedUser(): void
    {
        $user = $this->createUser('test@example.com', 'password123');
        $this->client->loginUser($user);

        $this->client->request('GET', '/post/create');
        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('Create a New Post', $this->client->getResponse()->getContent());
    }

    public function testCreatePostWithValidData(): void
    {
        $user = $this->createUser('test@example.com', 'password123');
        $this->client->loginUser($user);

        $this->client->request('POST', '/post/create', [
            'post' => [
                'content' => 'This is my first post!',
                'image' => '',
                'submit' => 'Post',
            ],
        ]);

        $this->assertResponseRedirects('/post/feed');
        $this->client->followRedirect();
        $this->assertStringContainsString('Post created successfully!', $this->client->getResponse()->getContent());
    }

    public function testCreatePostWithEmptyContent(): void
    {
        $user = $this->createUser('test@example.com', 'password123');
        $this->client->loginUser($user);

        $this->client->request('POST', '/post/create', [
            'post' => [
                'content' => '',
                'image' => '',
                'submit' => 'Post',
            ],
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('Post content cannot be empty', $this->client->getResponse()->getContent());
    }

    public function testFeedPageLoadsForAuthenticatedUser(): void
    {
        $user = $this->createUser('test@example.com', 'password123');
        $this->client->loginUser($user);

        $this->client->request('GET', '/post/feed');
        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('Post Feed', $this->client->getResponse()->getContent());
    }

    public function testFeedPageRequiresLogin(): void
    {
        $this->client->request('GET', '/post/feed');
        $this->assertResponseRedirects('/login');
    }

    public function testViewPostPageLoadsForAuthenticatedUser(): void
    {
        $user = $this->createUser('test@example.com', 'password123');
        $this->client->loginUser($user);

        $post = $this->createPost($user, 'Test post content');

        $this->client->request('GET', '/post/' . $post->getId() . '/view');
        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('Test post content', $this->client->getResponse()->getContent());
    }

    public function testEditPostAsAuthor(): void
    {
        $user = $this->createUser('test@example.com', 'password123');
        $this->client->loginUser($user);

        $post = $this->createPost($user, 'Original content');

        $this->client->request('GET', '/post/' . $post->getId() . '/edit');
        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('Edit Post', $this->client->getResponse()->getContent());

        $this->client->request('POST', '/post/' . $post->getId() . '/edit', [
            'post' => [
                'content' => 'Updated content',
                'image' => '',
                'submit' => 'Update Post',
            ],
        ]);

        $this->assertResponseRedirects('/post/' . $post->getId() . '/view');
    }

    public function testEditPostAsNonAuthor(): void
    {
        $author = $this->createUser('author@example.com', 'password123');
        $other = $this->createUser('other@example.com', 'password123');
        $this->client->loginUser($other);

        $post = $this->createPost($author, 'Original content');

        $this->client->request('GET', '/post/' . $post->getId() . '/edit');
        $this->assertResponseStatusCodeSame(403);
    }

    public function testDeletePostAsAuthor(): void
    {
        $user = $this->createUser('test@example.com', 'password123');
        $this->client->loginUser($user);

        $post = $this->createPost($user, 'Post to delete');

        $this->client->request('POST', '/post/' . $post->getId() . '/delete', [
            '_token' => $this->generateCsrfToken('delete' . $post->getId()),
        ]);

        $this->assertResponseRedirects('/post/feed');
    }

    public function testDeletePostAsNonAuthor(): void
    {
        $author = $this->createUser('author@example.com', 'password123');
        $other = $this->createUser('other@example.com', 'password123');
        $this->client->loginUser($other);

        $post = $this->createPost($author, 'Post content');

        $this->client->request('POST', '/post/' . $post->getId() . '/delete', [
            '_token' => $this->generateCsrfToken('delete' . $post->getId()),
        ]);

        $this->assertResponseStatusCodeSame(403);
    }

    public function testLikePost(): void
    {
        $user = $this->createUser('test@example.com', 'password123');
        $author = $this->createUser('author@example.com', 'password123');
        $this->client->loginUser($user);

        $post = $this->createPost($author, 'Post content');

        $this->client->request('POST', '/post/' . $post->getId() . '/like', [
            '_token' => $this->generateCsrfToken('like' . $post->getId()),
        ]);

        $this->assertResponseRedirects('/post/' . $post->getId() . '/view');
    }

    public function testAddCommentToPost(): void
    {
        $user = $this->createUser('test@example.com', 'password123');
        $author = $this->createUser('author@example.com', 'password123');
        $this->client->loginUser($user);

        $post = $this->createPost($author, 'Post content');

        $this->client->request('POST', '/post/' . $post->getId() . '/view', [
            'comment' => [
                'content' => 'Great post!',
                'submit' => 'Comment',
            ],
        ]);

        $this->assertResponseRedirects('/post/' . $post->getId() . '/view');
        $this->client->followRedirect();
        $this->assertStringContainsString('Comment added successfully!', $this->client->getResponse()->getContent());
    }

    // Helper methods

    private function createUser(string $email, string $password): User
    {
        $em = self::getContainer()->get('doctrine.orm.entity_manager');
        $user = new User();
        $user->setEmail($email);
        $user->setFirstName('Test');
        $user->setLastName('User');
        $user->setPassword(password_hash($password, PASSWORD_BCRYPT));
        $user->setIsEmailVerified(true);
        $user->setIsActive(true);
        $user->setIsBanned(false);

        $em->persist($user);
        $em->flush();

        return $user;
    }

    private function createPost(User $author, string $content): Post
    {
        $em = self::getContainer()->get('doctrine.orm.entity_manager');
        $post = new Post();
        $post->setContent($content);
        $post->setAuthor($author);

        $em->persist($post);
        $em->flush();

        return $post;
    }

    private function generateCsrfToken(string $tokenId): string
    {
        $tokenGenerator = self::getContainer()->get('security.csrf.token_manager');
        return $tokenGenerator->getToken($tokenId)->getValue();
    }
}