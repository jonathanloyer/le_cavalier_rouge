<?php

namespace App\Repository;

use App\Entity\Club;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Club>
 */
class ClubRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        // Je configure le dépôt pour gérer l'entité Club.
        parent::__construct($registry, Club::class);
    }

    function save(Club $newClub, ?bool $flush = false)
    {
        // Je sauvegarde un nouvel objet Club dans la base de données.
        $this->getEntityManager()->persist($newClub);
        if ($flush) {
            // J'effectue un flush pour enregistrer immédiatement les modifications dans la base de données.
            $this->getEntityManager()->flush();
        }
        // Je retourne le club sauvegardé.
        return $newClub;
    }

    //    /**
    //     * @return Club[] Returns an array of Club objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        // Je retourne une liste d'objets Club filtrés selon une condition spécifique.
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Club
    //    {
    //        // Je retourne un club correspondant à une condition spécifique ou null si aucun résultat trouvé.
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
