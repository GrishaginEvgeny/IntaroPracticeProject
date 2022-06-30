<?php

namespace App\Repository;

use App\Entity\Section;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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
}
