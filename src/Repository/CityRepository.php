<?php

namespace App\Repository;

use App\Entity\City;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<City>
 */
class CityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, City::class);
    }

    /**
     * @return string[]
     */
    public function fetchNames(): array
    {
        $rows = $this->createQueryBuilder('city')
            ->select('city.name')
            ->orderBy('city.name', 'ASC')
            ->getQuery()
            ->getScalarResult();

        return array_column($rows, 'name');
    }
}
