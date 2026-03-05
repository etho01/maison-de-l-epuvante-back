<?php

namespace App\Tests\Controller;

use App\Tests\WebTestCase;

class HealthCheckControllerTest extends WebTestCase
{
    public function testHealthCheck(): void
    {
        $client = static::createClient();
        
        $client->request('GET', '/api/health');

        $this->assertResponseStatusCodeSame(200);
        $this->assertResponseIsSuccessful();
        
        $data = json_decode($client->getResponse()->getContent(), true);
        
        $this->assertArrayHasKey('status', $data);
        $this->assertArrayHasKey('application', $data);
        $this->assertArrayHasKey('timestamp', $data);
        $this->assertArrayHasKey('database', $data);
        
        $this->assertEquals('ok', $data['status']);
        $this->assertEquals('Maison de l\'Épouvante API', $data['application']);
        $this->assertEquals('connected', $data['database']);
    }

    public function testInstallCheck(): void
    {
        $client = static::createClient();
        
        $client->request('GET', '/api/install/check');

        $this->assertResponseIsSuccessful();
        
        $data = json_decode($client->getResponse()->getContent(), true);
        
        $this->assertArrayHasKey('status', $data);
        $this->assertArrayHasKey('application', $data);
        $this->assertArrayHasKey('checks', $data);
        
        // Vérifier que les checks sont présents
        $this->assertArrayHasKey('database', $data['checks']);
        $this->assertArrayHasKey('tables', $data['checks']);
        $this->assertArrayHasKey('filesystem', $data['checks']);
        
        // Vérifier la structure de chaque check
        $this->assertArrayHasKey('status', $data['checks']['database']);
        $this->assertArrayHasKey('message', $data['checks']['database']);
        
        $this->assertEquals('Maison de l\'Épouvante API', $data['application']);
        $this->assertContains($data['status'], ['installed', 'incomplete', 'error']);
    }
}
