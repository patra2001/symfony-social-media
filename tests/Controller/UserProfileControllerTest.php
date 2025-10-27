<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class UserProfileControllerTest extends WebTestCase
{
    private $client;
    private $entityManager;
    private $testUser;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = self::getContainer()->get('doctrine.orm.entity_manager');

        // Create a test user
        $this->testUser = new User();
        $this->testUser->setEmail('testuser@example.com');
        $this->testUser->setPassword('$2y$13$.OpVEd3mD8GzSLEd5QzZWO8uYm5LV.ozZ8dFZ6qY9l');
        $this->testUser->setFirstName('Test');
        $this->testUser->setLastName('User');
        $this->testUser->setEmailVerified(true);
        $this->testUser->setIsActive(true);

        $this->entityManager->persist($this->testUser);
        $this->entityManager->flush();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
    }

    public function testViewProfilePageForLoggedInUser(): void
    {
        $this->client->loginUser($this->testUser);
        $this->client->request('GET', '/profile/' . $this->testUser->getId());

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('Test User', $this->client->getResponse()->getContent());
        $this->assertStringContainsString('testuser@example.com', $this->client->getResponse()->getContent());
    }

    public function testViewProfilePageForAnonymousUser(): void
    {
        $this->client->request('GET', '/profile/' . $this->testUser->getId());

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('Test User', $this->client->getResponse()->getContent());
    }

    public function testEditProfilePageRequiresLogin(): void
    {
        $this->client->request('GET', '/profile/edit');

        $this->assertResponseRedirects('/login');
    }

    public function testEditProfilePageForLoggedInUser(): void
    {
        $this->client->loginUser($this->testUser);
        $this->client->request('GET', '/profile/edit');

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('Edit Profile', $this->client->getResponse()->getContent());
        $this->assertStringContainsString('Test', $this->client->getResponse()->getContent());
    }

    public function testEditProfileWithValidData(): void
    {
        $this->client->loginUser($this->testUser);
        $this->client->request('GET', '/profile/edit');
        $this->assertResponseIsSuccessful();

        $form = $this->client->getCrawler()->selectButton('Save Changes')->form();
        $form['user_profile[firstName]'] = 'Updated';
        $form['user_profile[lastName]'] = 'Name';
        $form['user_profile[bio]'] = 'New bio';

        $this->client->submit($form);

        $this->assertResponseRedirects('/profile/' . $this->testUser->getId());

        // Refresh user from database
        $this->entityManager->refresh($this->testUser);

        $this->assertEquals('Updated', $this->testUser->getFirstName());
        $this->assertEquals('Name', $this->testUser->getLastName());
        $this->assertEquals('New bio', $this->testUser->getBio());
    }

    public function testEditProfileWithInvalidFirstName(): void
    {
        $this->client->loginUser($this->testUser);
        $this->client->request('GET', '/profile/edit');

        $form = $this->client->getCrawler()->selectButton('Save Changes')->form();
        $form['user_profile[firstName]'] = '';
        $form['user_profile[lastName]'] = 'Name';

        $this->client->submit($form);

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('First name is required', $this->client->getResponse()->getContent());
    }

    public function testEditProfileWithBioTooLong(): void
    {
        $this->client->loginUser($this->testUser);
        $this->client->request('GET', '/profile/edit');

        $form = $this->client->getCrawler()->selectButton('Save Changes')->form();
        $form['user_profile[firstName]'] = 'Test';
        $form['user_profile[lastName]'] = 'User';
        $form['user_profile[bio]'] = str_repeat('a', 1001);

        $this->client->submit($form);

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('Bio cannot exceed 1000 characters', $this->client->getResponse()->getContent());
    }

    public function testFollowUserRequiresLogin(): void
    {
        $this->client->request('POST', '/profile/' . $this->testUser->getId() . '/follow');

        $this->assertResponseRedirects('/login');
    }

    public function testFollowUser(): void
    {
        // Create another user
        $anotherUser = new User();
        $anotherUser->setEmail('another@example.com');
        $anotherUser->setPassword('$2y$13$hash');
        $anotherUser->setFirstName('Another');
        $anotherUser->setLastName('User');
        $anotherUser->setEmailVerified(true);

        $this->entityManager->persist($anotherUser);
        $this->entityManager->flush();

        $this->client->loginUser($this->testUser);
        $this->client->request('POST', '/profile/' . $anotherUser->getId() . '/follow', [
            '_token' => $this->client->getContainer()->get('security.csrf.token_manager')->getToken('follow')->getValue(),
        ]);

        $this->assertResponseRedirects('/profile/' . $anotherUser->getId());

        // Refresh user from database
        $this->entityManager->refresh($this->testUser);

        $this->assertTrue($this->testUser->getFollowing()->contains($anotherUser));
    }

    public function testCannotFollowYourself(): void
    {
        $this->client->loginUser($this->testUser);
        $this->client->request('POST', '/profile/' . $this->testUser->getId() . '/follow', [
            '_token' => $this->client->getContainer()->get('security.csrf.token_manager')->getToken('follow')->getValue(),
        ]);

        $this->assertResponseRedirects('/profile/' . $this->testUser->getId());

        // Refresh user from database
        $this->entityManager->refresh($this->testUser);

        $this->assertFalse($this->testUser->getFollowing()->contains($this->testUser));
    }

    public function testUnfollowUser(): void
    {
        // Create another user
        $anotherUser = new User();
        $anotherUser->setEmail('another@example.com');
        $anotherUser->setPassword('$2y$13$hash');
        $anotherUser->setFirstName('Another');
        $anotherUser->setLastName('User');
        $anotherUser->setEmailVerified(true);

        $this->entityManager->persist($anotherUser);
        $this->entityManager->flush();

        // Follow the user first
        $this->testUser->addFollowing($anotherUser);
        $this->entityManager->flush();

        $this->assertTrue($this->testUser->getFollowing()->contains($anotherUser));

        // Now unfollow
        $this->client->loginUser($this->testUser);
        $this->client->request('POST', '/profile/' . $anotherUser->getId() . '/unfollow', [
            '_token' => $this->client->getContainer()->get('security.csrf.token_manager')->getToken('unfollow')->getValue(),
        ]);

        $this->assertResponseRedirects('/profile/' . $anotherUser->getId());

        // Refresh user from database
        $this->entityManager->refresh($this->testUser);

        $this->assertFalse($this->testUser->getFollowing()->contains($anotherUser));
    }

    public function testProfileDisplaysUsersPosts(): void
    {
        $this->client->loginUser($this->testUser);
        $this->client->request('GET', '/profile/' . $this->testUser->getId());

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('Posts', $this->client->getResponse()->getContent());
    }

    public function testProfileShowsFollowerStats(): void
    {
        $this->client->loginUser($this->testUser);
        $this->client->request('GET', '/profile/' . $this->testUser->getId());

        $this->assertResponseIsSuccessful();
        $content = $this->client->getResponse()->getContent();
        $this->assertStringContainsString('Followers', $content);
        $this->assertStringContainsString('Following', $content);
    }

    public function testInvalidCsrfTokenFollowFails(): void
    {
        $anotherUser = new User();
        $anotherUser->setEmail('another@example.com');
        $anotherUser->setPassword('$2y$13$hash');
        $anotherUser->setFirstName('Another');
        $anotherUser->setLastName('User');
        $anotherUser->setEmailVerified(true);

        $this->entityManager->persist($anotherUser);
        $this->entityManager->flush();

        $this->client->loginUser($this->testUser);
        $this->client->request('POST', '/profile/' . $anotherUser->getId() . '/follow', [
            '_token' => 'invalid_token',
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }
}