<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;

class AuthControllerTest extends WebTestCase
{
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
    }

    public function testRegistrationPageLoads(): void
    {
        $client = static::createClient();
        $client->request('GET', '/auth/register');

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('Create Account', $client->getResponse()->getContent());
    }

    public function testLoginPageLoads(): void
    {
        $client = static::createClient();
        $client->request('GET', '/auth/login');

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('Sign In', $client->getResponse()->getContent());
    }

    public function testRegisterWithValidData(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/auth/register');

        $form = $crawler->selectButton('Register')->form([
            'registration_form_type[email]' => 'newuser@example.com',
            'registration_form_type[firstName]' => 'John',
            'registration_form_type[lastName]' => 'Doe',
            'registration_form_type[plainPassword]' => 'SecurePassword123',
            'registration_form_type[agreeTerms]' => true,
        ]);

        $client->submit($form);

        // Should redirect to login after successful registration
        $this->assertResponseRedirects('/auth/login');

        // Verify user was created in database
        $userRepository = static::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneBy(['email' => 'newuser@example.com']);
        $this->assertNotNull($user);
        $this->assertEquals('John', $user->getFirstName());
        $this->assertEquals('Doe', $user->getLastName());
        $this->assertFalse($user->isEmailVerified());
    }

    public function testRegisterWithDuplicateEmail(): void
    {
        // Create existing user
        $user = new User();
        $user->setEmail('existing@example.com');
        $user->setFirstName('Existing');
        $user->setLastName('User');
        $user->setPassword('hashed_password');
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $client = static::createClient();
        $crawler = $client->request('GET', '/auth/register');

        $form = $crawler->selectButton('Register')->form([
            'registration_form_type[email]' => 'existing@example.com',
            'registration_form_type[firstName]' => 'John',
            'registration_form_type[lastName]' => 'Doe',
            'registration_form_type[plainPassword]' => 'SecurePassword123',
            'registration_form_type[agreeTerms]' => true,
        ]);

        $client->submit($form);

        // Should redirect with error
        $this->assertResponseRedirects('/auth/register');
    }

    public function testLoginWithValidCredentials(): void
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/auth/login');
        $form = $crawler->selectButton('Sign In')->form([
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $client->submit($form);

        // If user doesn't exist, should stay on login page
        // (We'll implement proper login test with fixtures in Phase 5)
    }

    public function testRedirectAlreadyAuthenticatedUser(): void
    {
        // This test would require proper authentication setup
        // We'll implement it in Phase 5
    }
}