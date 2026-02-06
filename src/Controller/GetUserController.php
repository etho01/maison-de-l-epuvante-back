<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class GetUserController extends AbstractController
{
    public function __invoke(User $data): JsonResponse
    {
        return $this->json($data, 200, [], ['groups' => ['user:read', 'user:detail']]);
    }
}
