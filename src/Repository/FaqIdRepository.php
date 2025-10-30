<?php

namespace App\Repository;

use App\Entity\FaqId;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FaqId>
 *
 * @method FaqId|null find($id, $lockMode = null, $lockVersion = null)
 * @method FaqId|null findOneBy(array $criteria, array $orderBy = null)
 * @method FaqId[]    findAll()
 * @method FaqId[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FaqIdRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FaqId::class);
    }

//    /**
//     * @return FaqId[] Returns an array of FaqId objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('f.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?FaqId
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
