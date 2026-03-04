<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;

class HealthCheckController extends AbstractController
{
    #[Route('/api/health', name: 'app_health_check', methods: ['GET'])]
    public function check(EntityManagerInterface $entityManager): JsonResponse
    {
        $status = [
            'status' => 'ok',
            'application' => 'Maison de l\'Épouvante API',
            'timestamp' => date('Y-m-d H:i:s'),
        ];

        // Vérification de la connexion à la base de données
        try {
            $entityManager->getConnection()->executeQuery('SELECT 1');
            $status['database'] = 'connected';
        } catch (\Exception $e) {
            $status['database'] = 'disconnected';
            $status['status'] = 'error';
            $status['database_error'] = $e->getMessage();
            
            return $this->json($status, JsonResponse::HTTP_SERVICE_UNAVAILABLE);
        }

        return $this->json($status, JsonResponse::HTTP_OK);
    }

    #[Route('/api/install/check', name: 'app_install_check', methods: ['GET'])]
    public function installCheck(EntityManagerInterface $entityManager): JsonResponse
    {
        $checks = [
            'status' => 'installed',
            'application' => 'Maison de l\'Épouvante API',
            'checks' => [],
        ];

        // Vérification de la base de données
        try {
            $entityManager->getConnection()->executeQuery('SELECT 1');
            $checks['checks']['database'] = [
                'status' => 'ok',
                'message' => 'Base de données connectée'
            ];
        } catch (\Exception $e) {
            $checks['checks']['database'] = [
                'status' => 'error',
                'message' => 'Impossible de se connecter à la base de données',
                'error' => $e->getMessage()
            ];
            $checks['status'] = 'error';
        }

        // Vérification des tables principales
        try {
            $schemaManager = $entityManager->getConnection()->createSchemaManager();
            $tables = $schemaManager->listTableNames();
            
            $requiredTables = [
                    0 => "carts",
                    1 => "cart_items",
                    2 => "categories",
                    3 => "digital_contents",
                    4 => "doctrine_migration_versions",
                    5 => "orders",
                    6 => "order_items",
                    7 => "products",
                    8 => "reset_password_request",
                    9 => "subscriptions",
                    10 => "subscription_plans",
                    11 => "user",
            ];
            $missingTables = [];
            
            foreach ($requiredTables as $table) {
                if (!in_array($table, $tables)) {
                    $missingTables[] = $table;
                }
            }
            
            if (empty($missingTables)) {
                $checks['checks']['tables'] = [
                    'status' => 'ok',
                    'message' => 'Toutes les tables requises sont présentes',
                    'count' => count($tables)
                ];
            } else {
                $checks['checks']['tables'] = [
                    'status' => 'warning',
                    'message' => 'Tables manquantes',
                    'missing' => $missingTables
                ];
                $checks['status'] = 'incomplete';
            }
        } catch (\Exception $e) {
            $checks['checks']['tables'] = [
                'status' => 'error',
                'message' => 'Impossible de vérifier les tables',
                'error' => $e->getMessage()
            ];
            if ($checks['status'] !== 'error') {
                $checks['status'] = 'error';
            }
        }

        // Vérification du répertoire var/
        $varDir = $this->getParameter('kernel.project_dir') . '/var';
        $checks['checks']['filesystem'] = [
            'status' => is_writable($varDir) ? 'ok' : 'error',
            'message' => is_writable($varDir) 
                ? 'Répertoire var/ accessible en écriture' 
                : 'Répertoire var/ non accessible en écriture'
        ];
        
        if (!is_writable($varDir) && $checks['status'] !== 'error') {
            $checks['status'] = 'warning';
        }

        $httpCode = match ($checks['status']) {
            'installed' => JsonResponse::HTTP_OK,
            'incomplete' => JsonResponse::HTTP_PARTIAL_CONTENT,
            default => JsonResponse::HTTP_SERVICE_UNAVAILABLE,
        };

        return $this->json($checks, $httpCode);
    }
}
