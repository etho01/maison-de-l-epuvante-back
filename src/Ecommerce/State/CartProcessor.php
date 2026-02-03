<?php

namespace App\Ecommerce\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Doctrine\ORM\EntityManagerInterface;

class CartProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if ($operation->getMethod() === 'DELETE') {
            $this->entityManager->remove($data);
            $this->entityManager->flush();
            return null;
        }

        if (!$data->getId()) {
            $data->setCreatedAt(new \DateTimeImmutable());
        }
        $data->setUpdatedAt(new \DateTimeImmutable());

        $this->entityManager->persist($data);
        $this->entityManager->flush();

        return $data;
    }
}
