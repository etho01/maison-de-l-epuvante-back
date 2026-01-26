<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AuthControllerTest extends WebTestCase
{
    public function testCreateUser(): void
    {
        $client = static::createClient();
        
        $client->request('POST', '/api/users', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => 'test' . time() . '@example.com',
            'plainPassword' => 'password123',
            'firstName' => 'Test',
            'lastName' => 'User'
        ]));

        $this->assertResponseStatusCodeSame(201);
    }

    public function testLoginWithoutVerification(): void
    {
        $client = static::createClient();
        
        // Créer un utilisateur
        $email = 'test' . time() . '@example.com';
        $client->request('POST', '/api/users', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => $email,
            'plainPassword' => 'password123',
        ]));

        // Essayer de se connecter sans vérification
        $client->request('POST', '/api/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => $email,
            'password' => 'password123',
        ]));

        // Devrait être refusé car email non vérifié
        $this->assertResponseStatusCodeSame(403);
    }
}
