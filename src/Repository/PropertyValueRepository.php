<?php

namespace App\Repository;

use App\Entity\PropertyValue;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PropertyValue|null find($id, $lockMode = null, $lockVersion = null)
 * @method PropertyValue|null findOneBy(array $criteria, array $orderBy = null)
 * @method PropertyValue[]    findAll()
 * @method PropertyValue[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PropertyValueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PropertyValue::class);
    }
}
