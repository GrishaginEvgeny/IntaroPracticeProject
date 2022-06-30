<?php

namespace App\Repository;

use App\Entity\Section;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Section|null find($id, $lockMode = null, $lockVersion = null)
 * @method Section|null findOneBy(array $criteria, array $orderBy = null)
 * @method Section[]    findAll()
 * @method Section[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SectionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Section::class);
    }

    /**
     * @return array
     * Разделы первого уровня
     */
    public function firstLevelCatalogSections(): array
    {
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
          'SELECT s.id AS id, 
                   s.name AS name 
               FROM App\Entity\Section s
               WHERE s.parent IS NULL'
        );

        return $query->getResult();
    }

    /**
     * @param int $id
     * @return array
     * Дочерние секции раздела
     */
    public function childSections(int $id): array
    {
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            'SELECT s.id AS id, 
                   s.name AS name 
               FROM App\Entity\Section s
               WHERE s.parent = :id'
        )->setParameter('id', $id);

        return $query->getResult();
    }

    /**
     * @param int $id
     * @return array
     * @throws Exception
     */
    public function productsSection(int $id): array
    {
         $conn = $this->getEntityManager()->getConnection();

                $sql =
                    'SELECT o.name AS name,
                            o.price AS price,
                            o.picture AS picture,
                            o.id AS id
                     FROM section s,
                          product_section ps,
                          offer o
                     WHERE s.id = :id
                       AND s.id = ps.section_id
                       AND ps.product_id = o.product_id';

                $stmt = $conn->prepare($sql);
                $resultSet = $stmt->executeQuery(['id' => $id]);

                return $resultSet->fetchAllAssociative();
        // $entityManager = $this->getEntityManager();
        //
        //        $query = $entityManager->createQuery(
        //            'SELECT o.name AS name,
        //                    o.price AS price,
        //                    o.picture AS picture
        //             FROM App\Entity\Section s,
        //                  App\Entity\Product p,
        //                  App\Entity\Offer o
        //             WHERE s.id = :id
        //               AND s.id = p.sections
        //               AND p.id = o.product'
        //        )->setParameter('id', $id);
        //
        //        return $query->getResult();
    }
}
