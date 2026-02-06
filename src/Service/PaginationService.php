<?php

namespace App\Service;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;

class PaginationService
{
    /**
     * Pagine une requête et retourne un résultat structuré avec member et pagination
     *
     * @param QueryBuilder $queryBuilder Le QueryBuilder à paginer
     * @param int $page Le numéro de page (commence à 1)
     * @param int $itemsPerPage Le nombre d'éléments par page
     * @param bool $enablePagination Si false, retourne tous les éléments sans pagination
     * @return array Structure avec 'member' et 'pagination'
     */
    public function paginate(QueryBuilder $queryBuilder, int $page = 1, int $itemsPerPage = 30, bool $enablePagination = true): array
    {
        // Si la pagination est désactivée, retourner tous les éléments
        if (!$enablePagination) {
            return $this->getAllItems($queryBuilder);
        }

        // Assurer que la page est au minimum 1
        $page = max(1, $page);
        
        // Assurer que itemsPerPage est au minimum 1 et au maximum 100
        $itemsPerPage = max(1, min(100, $itemsPerPage));

        // Appliquer la pagination au QueryBuilder
        $queryBuilder
            ->setFirstResult(($page - 1) * $itemsPerPage)
            ->setMaxResults($itemsPerPage);

        // Créer le Paginator Doctrine
        $paginator = new Paginator($queryBuilder);
        
        // Compter le nombre total d'éléments
        $totalItems = count($paginator);
        
        // Calculer le nombre total de pages
        $totalPages = (int) ceil($totalItems / $itemsPerPage);
        
        // Récupérer les résultats
        $member = [];
        foreach ($paginator as $item) {
            $member[] = $item;
        }

        return [
            'member' => $member,
            'pagination' => [
                'page' => $page,
                'itemsPerPage' => $itemsPerPage,
                'totalItems' => $totalItems,
                'totalPages' => $totalPages,
                'hasNextPage' => $page < $totalPages,
                'hasPreviousPage' => $page > 1,
            ],
        ];
    }

    /**
     * Retourne tous les éléments sans pagination
     *
     * @param QueryBuilder $queryBuilder Le QueryBuilder
     * @return array Structure avec 'member' et 'pagination'
     */
    private function getAllItems(QueryBuilder $queryBuilder): array
    {
        $paginator = new Paginator($queryBuilder);
        $totalItems = count($paginator);
        
        $member = [];
        foreach ($paginator as $item) {
            $member[] = $item;
        }

        return [
            'member' => $member,
            'pagination' => [
                'page' => 1,
                'itemsPerPage' => $totalItems,
                'totalItems' => $totalItems,
                'totalPages' => 1,
                'hasNextPage' => false,
                'hasPreviousPage' => false,
            ],
        ];
    }

    /**
     * Pagine un tableau d'éléments et retourne un résultat structuré
     *
     * @param array $items Le tableau d'éléments à paginer
     * @param int $page Le numéro de page (commence à 1)
     * @param int $itemsPerPage Le nombre d'éléments par page
     * @param bool $enablePagination Si false, retourne tous les éléments sans pagination
     * @return array Structure avec 'member' et 'pagination'
     */
    public function paginateArray(array $items, int $page = 1, int $itemsPerPage = 30, bool $enablePagination = true): array
    {
        $totalItems = count($items);

        // Si la pagination est désactivée, retourner tous les éléments
        if (!$enablePagination) {
            return [
                'member' => $items,
                'pagination' => [
                    'page' => 1,
                    'itemsPerPage' => $totalItems,
                    'totalItems' => $totalItems,
                    'totalPages' => 1,
                    'hasNextPage' => false,
                    'hasPreviousPage' => false,
                ],
            ];
        }

        // Assurer que la page est au minimum 1
        $page = max(1, $page);
        
        // Assurer que itemsPerPage est au minimum 1 et au maximum 100
        $itemsPerPage = max(1, min(100, $itemsPerPage));

        $totalPages = (int) ceil($totalItems / $itemsPerPage);
        
        // Calculer l'offset
        $offset = ($page - 1) * $itemsPerPage;
        
        // Extraire la page demandée
        $member = array_slice($items, $offset, $itemsPerPage);

        return [
            'member' => $member,
            'pagination' => [
                'page' => $page,
                'itemsPerPage' => $itemsPerPage,
                'totalItems' => $totalItems,
                'totalPages' => $totalPages,
                'hasNextPage' => $page < $totalPages,
                'hasPreviousPage' => $page > 1,
            ],
        ];
    }
}
