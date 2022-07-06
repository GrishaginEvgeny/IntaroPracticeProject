<?php

namespace App\Repository;

use App\Entity\Offer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Offer|null find($id, $lockMode = null, $lockVersion = null)
 * @method Offer|null findOneBy(array $criteria, array $orderBy = null)
 * @method Offer[]    findAll()
 * @method Offer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OfferRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Offer::class);
    }


    /**
     * @throws Exception
     */
    public function getOfferInfo($id): array
    {
        $sql = 'SELECT o.id AS o_id, o.price, o.picture, p.*, pv.value
                FROM offer o, property_value pv, property p
                WHERE o.product_id = :id AND pv.offer_id = o.id AND pv.property_id = p.id';

        $connect = $this->getEntityManager()->getConnection();
        $stmt = $connect->prepare($sql);
        return $stmt->executeQuery(['id' => $id])->fetchAllAssociative();
    }
}
