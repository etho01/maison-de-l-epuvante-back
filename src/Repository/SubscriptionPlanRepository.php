<?php

namespace App\Repository;

use App\Entity\SubscriptionPlan;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class SubscriptionPlanRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SubscriptionPlan::class);
    }

    public function findActivePlans(): array
    {
        return $this->createQueryBuilder('sp')
            ->where('sp.active = :active')
            ->setParameter('active', true)
            ->orderBy('sp.price', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
