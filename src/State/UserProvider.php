<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\User as UserDto;
use App\Entity\User as UserEntity;
use App\Repository\UserRepository;

/**
 * Provider pour transformer les entités User en DTOs User
 */
class UserProvider implements ProviderInterface
{
    public function __construct(
        private UserRepository $userRepository
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        // Pour une collection
        if ($operation instanceof \ApiPlatform\Metadata\GetCollection) {
            $entities = $this->userRepository->findAll();
            return array_map(fn(UserEntity $entity) => $this->entityToDto($entity), $entities);
        }

        // Pour un seul utilisateur
        if (isset($uriVariables['id'])) {
            $entity = $this->userRepository->find($uriVariables['id']);
            if (!$entity) {
                return null;
            }
            return $this->entityToDto($entity);
        }

        return null;
    }

    /**
     * Transforme une entité User en DTO User
     */
    private function entityToDto(UserEntity $entity): UserDto
    {
        $dto = new UserDto();
        $dto->id = $entity->getId();
        $dto->email = $entity->getEmail();
        $dto->roles = $entity->getRoles();
        $dto->firstName = $entity->getFirstName();
        $dto->lastName = $entity->getLastName();
        $dto->isVerified = $entity->isVerified();
        $dto->createdAt = $entity->getCreatedAt();
        $dto->updatedAt = $entity->getUpdatedAt();

        return $dto;
    }
}
