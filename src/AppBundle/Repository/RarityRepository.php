<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

class RarityRepository extends EntityRepository
{
    public function findAll()
    {
        $qb = $this->createQueryBuilder('r')
            ->select('r')
            ->orderBy('r.name', 'ASC');

        return $qb->getQuery()->getResult();
    }
}
