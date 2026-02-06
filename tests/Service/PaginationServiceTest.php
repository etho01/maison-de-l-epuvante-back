<?php

namespace App\Tests\Service;

use App\Service\PaginationService;
use PHPUnit\Framework\TestCase;

class PaginationServiceTest extends TestCase
{
    private PaginationService $paginationService;

    protected function setUp(): void
    {
        $this->paginationService = new PaginationService();
    }

    public function testPaginateArray(): void
    {
        $items = range(1, 50); // 50 éléments
        $result = $this->paginationService->paginateArray($items, 1, 10);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('member', $result);
        $this->assertArrayHasKey('pagination', $result);
        
        // Vérifier les membres de la première page
        $this->assertCount(10, $result['member']);
        $this->assertEquals([1, 2, 3, 4, 5, 6, 7, 8, 9, 10], $result['member']);

        // Vérifier les métadonnées de pagination
        $this->assertEquals(1, $result['pagination']['page']);
        $this->assertEquals(10, $result['pagination']['itemsPerPage']);
        $this->assertEquals(50, $result['pagination']['totalItems']);
        $this->assertEquals(5, $result['pagination']['totalPages']);
        $this->assertTrue($result['pagination']['hasNextPage']);
        $this->assertFalse($result['pagination']['hasPreviousPage']);
    }

    public function testPaginateArraySecondPage(): void
    {
        $items = range(1, 50);
        $result = $this->paginationService->paginateArray($items, 2, 10);

        $this->assertCount(10, $result['member']);
        $this->assertEquals([11, 12, 13, 14, 15, 16, 17, 18, 19, 20], $result['member']);
        
        $this->assertEquals(2, $result['pagination']['page']);
        $this->assertTrue($result['pagination']['hasNextPage']);
        $this->assertTrue($result['pagination']['hasPreviousPage']);
    }

    public function testPaginateArrayLastPage(): void
    {
        $items = range(1, 50);
        $result = $this->paginationService->paginateArray($items, 5, 10);

        $this->assertCount(10, $result['member']);
        $this->assertEquals([41, 42, 43, 44, 45, 46, 47, 48, 49, 50], $result['member']);
        
        $this->assertEquals(5, $result['pagination']['page']);
        $this->assertFalse($result['pagination']['hasNextPage']);
        $this->assertTrue($result['pagination']['hasPreviousPage']);
    }

    public function testPaginateArrayWithoutPagination(): void
    {
        $items = range(1, 50);
        $result = $this->paginationService->paginateArray($items, 1, 10, false);

        $this->assertCount(50, $result['member']);
        $this->assertEquals(50, $result['pagination']['totalItems']);
        $this->assertEquals(1, $result['pagination']['totalPages']);
        $this->assertFalse($result['pagination']['hasNextPage']);
        $this->assertFalse($result['pagination']['hasPreviousPage']);
    }

    public function testPaginateArrayEmptyArray(): void
    {
        $result = $this->paginationService->paginateArray([], 1, 10);

        $this->assertCount(0, $result['member']);
        $this->assertEquals(0, $result['pagination']['totalItems']);
        $this->assertEquals(0, $result['pagination']['totalPages']);
        $this->assertFalse($result['pagination']['hasNextPage']);
        $this->assertFalse($result['pagination']['hasPreviousPage']);
    }

    public function testPaginateArrayInvalidPageNumber(): void
    {
        $items = range(1, 50);
        $result = $this->paginationService->paginateArray($items, 0, 10);

        // La page 0 doit être corrigée à 1
        $this->assertEquals(1, $result['pagination']['page']);
    }

    public function testPaginateArrayExcessiveItemsPerPage(): void
    {
        $items = range(1, 50);
        $result = $this->paginationService->paginateArray($items, 1, 200);

        // itemsPerPage doit être limité à 100
        $this->assertLessThanOrEqual(100, $result['pagination']['itemsPerPage']);
    }

    public function testPaginateArrayPartialLastPage(): void
    {
        $items = range(1, 25);
        $result = $this->paginationService->paginateArray($items, 3, 10);

        // La dernière page ne contient que 5 éléments
        $this->assertCount(5, $result['member']);
        $this->assertEquals([21, 22, 23, 24, 25], $result['member']);
        $this->assertEquals(3, $result['pagination']['totalPages']);
    }

    public function testPaginateArrayBeyondLastPage(): void
    {
        $items = range(1, 25);
        $result = $this->paginationService->paginateArray($items, 10, 10);

        // Au-delà de la dernière page, aucun élément n'est retourné
        $this->assertCount(0, $result['member']);
        $this->assertEquals(10, $result['pagination']['page']);
    }

    public function testPaginateArrayMinItemsPerPage(): void
    {
        $items = range(1, 10);
        $result = $this->paginationService->paginateArray($items, 1, 0);

        // itemsPerPage minimum est 1
        $this->assertGreaterThanOrEqual(1, $result['pagination']['itemsPerPage']);
    }
}
