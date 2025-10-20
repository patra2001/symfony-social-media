<?php

namespace App\Repository;

use App\Entity\Enquiry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Enquiry>
 *
 * @method Enquiry|null find($id, $lockMode = null, $lockVersion = null)
 * @method Enquiry|null findOneBy(array $criteria, array $orderBy = null)
 * @method Enquiry[]    findAll()
 * @method Enquiry[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EnquiryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Enquiry::class);
    }

//    /**
//     * @return Enquiry[] Returns an array of Enquiry objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('e.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Enquiry
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
