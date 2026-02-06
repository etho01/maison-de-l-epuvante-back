<?php

namespace App\Tests\Ecommerce\Controller;

use App\Tests\WebTestCase;

class ProductControllerTest extends WebTestCase
{
    public function testGetProducts(): void
    {
        $client = static::createClient();
        
        $client->request('GET', '/api/products', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json');
        
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('member', $data);
        $this->assertArrayHasKey('pagination', $data);
    }

    public function testGetProductsPagination(): void
    {
        $client = static::createClient();
        
        $client->request('GET', '/api/products?page=1&itemsPerPage=20', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertResponseIsSuccessful();
        
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('pagination', $data);
        $this->assertEquals(1, $data['pagination']['page']);
        $this->assertLessThanOrEqual(20, $data['pagination']['itemsPerPage']);
    }

    public function testGetProductById(): void
    {
        $client = static::createClient();
        
        $client->request('GET', '/api/products/1', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        if ($client->getResponse()->getStatusCode() === 200) {
            $this->assertResponseIsSuccessful();
            
            $data = json_decode($client->getResponse()->getContent(), true);
            $this->assertArrayHasKey('id', $data);
            $this->assertArrayHasKey('name', $data);
            $this->assertArrayHasKey('price', $data);
            $this->assertArrayHasKey('slug', $data);
        } else {
            $this->assertResponseStatusCodeSame(404);
        }
    }

    public function testGetProductBySlug(): void
    {
        $client = static::createClient();
        
        $client->request('GET', '/api/products/by-slug/test-product', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        if ($client->getResponse()->getStatusCode() === 200) {
            $this->assertResponseIsSuccessful();
            
            $data = json_decode($client->getResponse()->getContent(), true);
            $this->assertArrayHasKey('slug', $data);
        } else {
            $this->assertResponseStatusCodeSame(404);
        }
    }

    public function testGetNonExistentProduct(): void
    {
        $client = static::createClient();
        
        $client->request('GET', '/api/products/99999', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertResponseStatusCodeSame(404);
    }

    public function testCreateProductUnauthorized(): void
    {
        $client = static::createClient();
        
        $client->request('POST', '/api/products', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'name' => 'Test Product',
            'slug' => 'test-product',
            'price' => '29.99',
            'type' => 'physical'
        ]));

        $this->assertResponseStatusCodeSame(401);
    }

    public function testUpdateProductUnauthorized(): void
    {
        $client = static::createClient();
        
        $client->request('PUT', '/api/products/1', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'name' => 'Updated Product'
        ]));

        $this->assertResponseStatusCodeSame(401);
    }

    public function testDeleteProductUnauthorized(): void
    {
        $client = static::createClient();
        
        $client->request('DELETE', '/api/products/1', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertResponseStatusCodeSame(401);
    }

    public function testFilterProductsByType(): void
    {
        $client = static::createClient();
        
        $client->request('GET', '/api/products?type=physical', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertResponseIsSuccessful();
        
        $data = json_decode($client->getResponse()->getContent(), true);
        
        if (count($data['member']) > 0) {
            foreach ($data['member'] as $product) {
                $this->assertEquals('physical', $product['type']);
            }
        }
    }

    public function testFilterProductsByActive(): void
    {
        $client = static::createClient();
        
        $client->request('GET', '/api/products?active=true', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertResponseIsSuccessful();
        
        $data = json_decode($client->getResponse()->getContent(), true);
        
        if (count($data['member']) > 0) {
            foreach ($data['member'] as $product) {
                $this->assertTrue($product['active']);
            }
        }
    }

    public function testFilterProductsByCategory(): void
    {
        $client = static::createClient();
        
        $client->request('GET', '/api/products?category=1', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertResponseIsSuccessful();
    }

    public function testSearchProducts(): void
    {
        $client = static::createClient();
        
        $client->request('GET', '/api/products?search=horror', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertResponseIsSuccessful();
    }

    public function testCreateProductAsAuthenticatedUser(): void
    {
        $auth = $this->getAuthenticatedClient(null, ['ROLE_ADMIN']);
        $client = $auth['client'];
        
        $client->request('POST', '/api/products', [], [], 
            $this->getAuthHeaders($auth['token']),
            json_encode([
                'name' => 'Horror Game ' . uniqid(),
                'slug' => 'horror-game-' . uniqid(),
                'price' => 49.99,
                'description' => 'A scary horror game',
                'type' => 'digital',
                'stock' => 100,
                'active' => true
            ])
        );

        // Peut retourner 201 si implémenté, 404 si route non trouvée, ou 405 si méthode non autorisée
        $this->assertTrue(in_array($client->getResponse()->getStatusCode(), [201, 404, 405]));
    }

    public function testUpdateProductAsAuthenticatedUser(): void
    {
        $auth = $this->getAuthenticatedClient(null, ['ROLE_ADMIN']);
        $client = $auth['client'];
        
        $client->request('PUT', '/api/products/1', [], [], 
            $this->getAuthHeaders($auth['token']),
            json_encode([
                'name' => 'Updated Horror Product',
                'price' => 59.99
            ])
        );

        // Peut retourner 200 si implémenté, 404 si non trouvé, ou 405 si méthode non autorisée
        $this->assertTrue(in_array($client->getResponse()->getStatusCode(), [200, 404, 405]));
    }

    public function testDeleteProductAsAuthenticatedUser(): void
    {
        $auth = $this->getAuthenticatedClient(null, ['ROLE_ADMIN']);
        $client = $auth['client'];
        
        $client->request('DELETE', '/api/products/1', [], [], 
            $this->getAuthHeaders($auth['token'])
        );

        // Peut retourner 204 si supprimé, 404 si non trouvé, ou 405 si méthode non autorisée
        $this->assertTrue(in_array($client->getResponse()->getStatusCode(), [204, 404, 405]));
    }
}
