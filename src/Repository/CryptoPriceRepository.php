<?php

namespace App\Repository;

use App\Entity\CryptoPrice;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CryptoPriceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CryptoPrice::class);
    }

    public function clearTable(): void
    {
        $this->createQueryBuilder('c')
            ->delete()
            ->getQuery()
            ->execute();
    }
}
