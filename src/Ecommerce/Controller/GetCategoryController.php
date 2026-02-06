<?php

namespace App\Ecommerce\Controller;

use App\Ecommerce\Entity\Category;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class GetCategoryController extends AbstractController
{
    public function __invoke(Category $data): JsonResponse
    {
        return $this->json($data, 200, [], ['groups' => ['category:read', 'category:detail']]);
    }
}
