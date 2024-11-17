<?php

namespace App\Repository;

use App\Entity\FeuilleMatch;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FeuilleMatch>
 */
class FeuilleMatchRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FeuilleMatch::class);
    }
    function save(FeuilleMatch $newFeuilleMatch, ?bool $flush=false)
    {
        $this->getEntityManager()->persist($newFeuilleMatch);
        if ($flush){
            $this->getEntityManager()->flush();
        }
        return $newFeuilleMatch;
    }

//    /**
//     * @return FeuilleMatch[] Returns an array of FeuilleMatch objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('f.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?FeuilleMatch
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
