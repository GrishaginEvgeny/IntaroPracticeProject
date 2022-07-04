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
     * @throws Exception
     */
    public function getHeaderSections(): array
    {
        $sql = 'SELECT * 
                FROM section 
                WHERE parent_id IS NULL';

        $connect = $this->getEntityManager()->getConnection();
        $stmt = $connect->prepare($sql);
        $resultSet = $stmt->executeQuery()->fetchAllAssociative();
        for ($i = 0; $i < count($resultSet); $i++) {
            $resultSet[$i]['children'] = $this->getChildSections($resultSet[$i]['id']);
        }
        return $resultSet;
    }

    /**
     * @throws Exception
     */
    public function getChildSections(int $id): array
    {
        $sql = 'WITH RECURSIVE childs AS (
                    SELECT id,
                           parent_id,
                           name
                    FROM section
                    WHERE parent_id = :id
                
                    UNION ALL
                
                    SELECT s.id, 
                           s.parent_id, 
                           s.name
                    FROM section s
                        JOIN childs c
                        ON s.parent_id = c.id
                )
                SELECT * FROM childs';

        $connect = $this->getEntityManager()->getConnection();
        $stmt = $connect->prepare($sql);
        $resultSet = $stmt->executeQuery(['id' => $id]);
        return $resultSet->fetchAllAssociative();
    }

    /**
     * @throws Exception
     */
    public function getSectionOffers(int $id): array
    {
        $sql = 'WITH RECURSIVE childs AS (
                    SELECT id,
                           parent_id,
                           name
                    FROM section
                    WHERE id = :id
                    UNION ALL
                    SELECT s.id,
                           s.parent_id,
                           s.name
                    FROM section s
                        JOIN childs c
                        ON s.parent_id = c.id
                )
                SELECT DISTINCT ON (p.id) p.id, p.name, o.price, o.picture
                FROM childs c,
                     product p,
                     product_section ps,
                     offer o
                WHERE c.id = ps.section_id
                  AND p.id = ps.product_id
                  AND o.product_id = p.id';

        $connect = $this->getEntityManager()->getConnection();
        $stmt = $connect->prepare($sql);
        $resultSet = $stmt->executeQuery(['id' => $id]);
        return $stmt->executeQuery()->fetchAllAssociative();
    }
}
