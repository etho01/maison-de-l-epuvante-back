<?php

namespace App\State;

use ApiPlatform\Metadata\DeleteOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\User as UserDto;
use App\Entity\User as UserEntity;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

/**
 * Processor pour transformer les DTOs User en entités User et les persister
 */
class UserStateProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
        private UserRepository $userRepository,
        private VerifyEmailHelperInterface $verifyEmailHelper
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ?UserDto
    {
        if (!$data instanceof UserDto) {
            return null;
        }

        // Gestion de la suppression
        if ($operation instanceof DeleteOperationInterface) {
            $entity = $this->userRepository->find($data->id);
            if ($entity) {
                $this->entityManager->remove($entity);
                $this->entityManager->flush();
            }
            return null;
        }

        // Création ou mise à jour
        $entity = $this->dtoToEntity($data, $uriVariables);
        
        // Hash du mot de passe si fourni
        if ($data->plainPassword) {
            $hashedPassword = $this->passwordHasher->hashPassword(
                $entity,
                $data->plainPassword
            );
            $entity->setPassword($hashedPassword);
        }

        $isNew = $entity->getId() === null;

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        // Pour les nouveaux utilisateurs, générer le lien de vérification
        if ($isNew) {
            $signatureComponents = $this->verifyEmailHelper->generateSignature(
                'api_verify_email',
                $entity->getId(),
                $entity->getEmail()
            );

            // TODO: Envoyer l'email avec le lien de vérification
            // $signatureComponents->getSignedUrl()
        }

        // Retourner le DTO mis à jour
        return $this->entityToDto($entity);
    }

    /**
     * Transforme un DTO User en entité User
     */
    private function dtoToEntity(UserDto $dto, array $uriVariables): UserEntity
    {
        // Mise à jour d'un utilisateur existant
        if (isset($uriVariables['id'])) {
            $entity = $this->userRepository->find($uriVariables['id']);
            if (!$entity) {
                throw new \RuntimeException('User not found');
            }
        } else {
            // Création d'un nouvel utilisateur
            $entity = new UserEntity();
            $entity->setCreatedAt(new \DateTimeImmutable());
        }

        // Mapper les données du DTO vers l'entité
        if ($dto->email !== null) {
            $entity->setEmail($dto->email);
        }

        if ($dto->firstName !== null) {
            $entity->setFirstName($dto->firstName);
        }

        if ($dto->lastName !== null) {
            $entity->setLastName($dto->lastName);
        }

        // Mise à jour de la date de modification
        if ($entity->getId() !== null) {
            $entity->setUpdatedAt(new \DateTimeImmutable());
        }

        return $entity;
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
