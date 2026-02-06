<?php

namespace App\Tests;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as BaseWebTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

abstract class WebTestCase extends BaseWebTestCase
{
    protected static bool $schemaCreated = false;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        
        $kernel = static::createKernel();
        $kernel->boot();
        
        $application = new Application($kernel);
        $application->setAutoExit(false);
        
        $output = new NullOutput();

        // Drop and create database schema
        $application->run(new ArrayInput([
            'command' => 'doctrine:schema:drop',
            '--force' => true,
            '--full-database' => true,
            '--env' => 'test',
        ]), $output);

        $application->run(new ArrayInput([
            'command' => 'doctrine:schema:create',
            '--env' => 'test',
        ]), $output);

        $kernel->shutdown();
    }

    /**
     * Crée un client avec un utilisateur authentifié et retourne le token JWT
     */
    protected function getAuthenticatedClient(string $email = null, array $roles = ['ROLE_USER']): array
    {
        $email = $email ?? 'admin' . uniqid() . '@example.com';
        $password = 'Test1234!';

        $client = static::createClient();
        
        // Créer l'utilisateur via l'API
        $client->request('POST', '/api/users', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => $email,
            'password' => $password,
            'firstName' => 'Test',
            'lastName' => 'Admin',
            'roles' => $roles
        ]));

        // Récupérer l'utilisateur et le vérifier directement via le container du client
        $container = $client->getContainer();
        $em = $container->get('doctrine')->getManager();
        
        $user = $em->getRepository(User::class)->findOneBy(['email' => $email]);
        if ($user) {
            $user->setIsVerified(true);
            $em->flush();
        }

        // Se connecter et récupérer le token
        $client->request('POST', '/api/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => $email,
            'password' => $password,
        ]));

        $response = json_decode($client->getResponse()->getContent(), true);
        $token = $response['token'] ?? '';
        
        return ['client' => $client, 'token' => $token];
    }

    /**
     * Retourne les headers d'authentification avec le token JWT
     */
    protected function getAuthHeaders(string $token): array
    {
        return [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
        ];
    }
}
