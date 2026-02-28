<?php

namespace App\Controller\Auth;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use App\Entity\User;
use App\Enum\ApiError;
use App\Trait\ApiResponseTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AuthController extends AbstractController
{
    use ApiResponseTrait;

    public function login(#[CurrentUser] ?User $user): JsonResponse
    {
        if (null === $user) {
            return $this->errorResponse(401, ApiError::INVALID_CREDENTIALS);
        }

        if (!$user->isVerified()) {
            return $this->errorResponse(403, ApiError::EMAIL_NOT_VERIFIED);
        }

        return $this->json([
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'firstName' => $user->getFirstName(),
                'lastName' => $user->getLastName(),
                'roles' => $user->getRoles(),
            ]
        ]);
    }

    public function getCurrentUser(#[CurrentUser] ?User $user): JsonResponse
    {
        if (null === $user) {
            return $this->errorResponse(401, ApiError::USER_NOT_AUTHENTICATED);
        }

        return $this->json([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
            'roles' => $user->getRoles(),
            'isVerified' => $user->isVerified(),
            'createdAt' => $user->getCreatedAt()?->format('c'),
            'updatedAt' => $user->getUpdatedAt()?->format('c'),
        ]);
    }

    public function updateCurrentUser(
        Request $request,
        #[CurrentUser] ?User $user,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator
    ): JsonResponse {
        if (null === $user) {
            return $this->errorResponse(401, ApiError::USER_NOT_AUTHENTICATED);
        }

        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return $this->errorResponse(400, ApiError::INVALID_DATA);
        }

        // Update allowed fields only
        if (isset($data['firstName'])) {
            $user->setFirstName($data['firstName']);
        }

        if (isset($data['lastName'])) {
            $user->setLastName($data['lastName']);
        }

        if (isset($data['email'])) {
            $user->setEmail($data['email']);
        }

        // Validate the updated user
        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getPropertyPath() . ': ' . $error->getMessage();
            }
            return $this->errorResponse(400, array_merge([ApiError::VALIDATION_FAILED], $errorMessages));
        }

        $user->setUpdatedAt(new \DateTimeImmutable());
        $entityManager->flush();

        return $this->json([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
            'roles' => $user->getRoles(),
            'isVerified' => $user->isVerified(),
            'createdAt' => $user->getCreatedAt()?->format('c'),
            'updatedAt' => $user->getUpdatedAt()?->format('c'),
        ]);
    }
}
