<?php

namespace App\Ecommerce\Repository;

use App\Ecommerce\Entity\Delivery;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Delivery>
 */
class DeliveryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Delivery::class);
    }

    /**
     * Find delivery by order ID
     */
    public function findByOrderId(int $orderId): ?Delivery
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.order = :orderId')
            ->setParameter('orderId', $orderId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Find all deliveries with a specific status
     */
    public function findByStatus(string $status): array
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.status = :status')
            ->setParameter('status', $status)
            ->orderBy('d.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find deliveries by tracking number
     */
    public function findByTrackingNumber(string $trackingNumber): ?Delivery
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.trackingNumber = :trackingNumber')
            ->setParameter('trackingNumber', $trackingNumber)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
