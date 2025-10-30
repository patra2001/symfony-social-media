<?php

namespace App\Repository;

use App\Entity\State;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<State>
 */
class StateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, State::class);
    }

    /**
     * @return string[]
     */
    public function fetchNames(): array
    {
        $rows = $this->createQueryBuilder('state')
            ->select('state.name')
            ->orderBy('state.name', 'ASC')
            ->getQuery()
            ->getScalarResult();

        return array_column($rows, 'name');
    }
}
