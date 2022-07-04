<?php


use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManager;    
use Doctrine\ORM\Id\AbstractIdGenerator;

class UserIdGenerator extends AbstractIdGenerator
{
    /**
     *
     * @param EntityManager $em
     * @param \Doctrine\ORM\Mapping\Entity $entity
     * @return bool|string
     */
    public function generate(EntityManager $em, $entity)
    {
        if ($entity instanceof User) {
            $prefix = 18;
            (int)$maxCode = $em->getRepository(User::class)->findMaxCode();
            $code = $maxCode[1] > $prefix ? $maxCode[1] + 1 : $prefix + $maxCode[1];
            return $code;
        }
        return false;
    }
    
}