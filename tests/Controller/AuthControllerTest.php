<?php

namespace App\Tests\Controller;

use App\Tests\WebTestCase;

class AuthControllerTest extends WebTestCase
{
    public function testCreateUser(): void
    {
        $client = static::createClient();
        
        $client->request('POST', '/api/users', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => 'test' . time() . '@example.com',
            'password' => 'Password123!',
            'firstName' => 'Test',
            'lastName' => 'User'
        ]));

        $this->assertResponseStatusCodeSame(201);
    }

    public function testLoginWithoutVerification(): void
    {
        $client = static::createClient();
        
        // Créer un utilisateur
        $email = 'testlogin' . time() . '@example.com';
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

        // Le contrôleur AuthController renvoie 401 pour email non vérifié
        $this->assertResponseStatusCodeSame(401);
    }
}
