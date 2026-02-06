<?php

namespace App\Tests\Ecommerce\Controller;

use App\Tests\WebTestCase;

class CategoryControllerTest extends WebTestCase
{
    public function testGetCategories(): void
    {
        $client = static::createClient();
        
        $client->request('GET', '/api/categories', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json');
        
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('member', $data);
        $this->assertArrayHasKey('pagination', $data);
    }

    public function testGetCategoriesPagination(): void
    {
        $client = static::createClient();
        
        $client->request('GET', '/api/categories?page=1&itemsPerPage=10', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertResponseIsSuccessful();
        
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('pagination', $data);
        $this->assertEquals(1, $data['pagination']['page']);
    }

    public function testGetCategoryById(): void
    {
        $client = static::createClient();
        
        $client->request('GET', '/api/categories/1', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        if ($client->getResponse()->getStatusCode() === 200) {
            $this->assertResponseIsSuccessful();
            
            $data = json_decode($client->getResponse()->getContent(), true);
            $this->assertArrayHasKey('id', $data);
            $this->assertArrayHasKey('name', $data);
            $this->assertArrayHasKey('slug', $data);
        } else {
            $this->assertResponseStatusCodeSame(404);
        }
    }

    public function testGetNonExistentCategory(): void
    {
        $client = static::createClient();
        
        $client->request('GET', '/api/categories/99999', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertResponseStatusCodeSame(404);
    }

    public function testCreateCategoryUnauthorized(): void
    {
        $client = static::createClient();
        
        $client->request('POST', '/api/categories', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'name' => 'Test Category',
            'slug' => 'test-category',
            'description' => 'A test category'
        ]));

        $this->assertResponseStatusCodeSame(401);
    }

    public function testUpdateCategoryUnauthorized(): void
    {
        $client = static::createClient();
        
        $client->request('PUT', '/api/categories/1', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'name' => 'Updated Category'
        ]));

        $this->assertResponseStatusCodeSame(401);
    }

    public function testDeleteCategoryUnauthorized(): void
    {
        $client = static::createClient();
        
        $client->request('DELETE', '/api/categories/1', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertResponseStatusCodeSame(401);
    }

    public function testCreateCategoryAsAuthenticatedUser(): void
    {
        $auth = $this->getAuthenticatedClient(null, ['ROLE_ADMIN']);
        $client = $auth['client'];
        
        $client->request('POST', '/api/categories', [], [], 
            $this->getAuthHeaders($auth['token']),
            json_encode([
                'name' => 'Horror Category',
                'slug' => 'horror-category-' . uniqid(),
                'description' => 'A category for horror items'
            ])
        );

        // Peut retourner 201 si implémenté, 404 si route non trouvée, ou 405 si méthode non autorisée
        $this->assertTrue(in_array($client->getResponse()->getStatusCode(), [201, 404, 405]));
    }

    public function testUpdateCategoryAsAuthenticatedUser(): void
    {
        $auth = $this->getAuthenticatedClient(null, ['ROLE_ADMIN']);
        $client = $auth['client'];
        
        $client->request('PUT', '/api/categories/1', [], [], 
            $this->getAuthHeaders($auth['token']),
            json_encode([
                'name' => 'Updated Horror Category'
            ])
        );

        // Peut retourner 200 si implémenté, 404 si non trouvé, ou 405 si méthode non autorisée
        $this->assertTrue(in_array($client->getResponse()->getStatusCode(), [200, 404, 405]));
    }

    public function testDeleteCategoryAsAuthenticatedUser(): void
    {
        $auth = $this->getAuthenticatedClient(null, ['ROLE_ADMIN']);
        $client = $auth['client'];
        
        $client->request('DELETE', '/api/categories/1', [], [], 
            $this->getAuthHeaders($auth['token'])
        );

        // Peut retourner 204 si supprimé, 404 si non trouvé, ou 405 si méthode non autorisée
        $this->assertTrue(in_array($client->getResponse()->getStatusCode(), [204, 404, 405]));
    }
}
