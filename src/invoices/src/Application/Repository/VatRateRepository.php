<?php

namespace App\Application\Repository;

use App\Application\Entity\VatRate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method VatRate|null find($id, $lockMode = null, $lockVersion = null)
 * @method VatRate|null findOneBy(array $criteria, array $orderBy = null)
 * @method VatRate[]    findAll()
 * @method VatRate[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VatRateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VatRate::class);
    }

    // /**
    //  * @return VatRate[] Returns an array of VatRate objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('v.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?VatRate
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
