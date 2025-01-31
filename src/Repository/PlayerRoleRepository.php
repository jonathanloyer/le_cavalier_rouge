<?php

namespace App\Repository;

use App\Entity\PlayerRole;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PlayerRole>
 */
class PlayerRoleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        // Je configure le dépôt pour gérer l'entité PlayerRole.
        parent::__construct($registry, PlayerRole::class);
    }

    function save(PlayerRole $newRole, ?bool $flush=false)
    {
        // Je sauvegarde un nouveau rôle dans la base de données.
        $this->getEntityManager()->persist($newRole);
        if ($flush){
            // Je valide les modifications dans la base de données si flush est activé.
            $this->getEntityManager()->flush();
        }
        return $newRole; // Je retourne le rôle sauvegardé.
    }

    //    /**
    //     * @return Role[] Returns an array of Role objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        // Je retourne une liste de rôles filtrés par un champ spécifique.
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('r.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Role
    //    {
    //        // Je retourne un rôle correspondant à une condition spécifique ou null si aucun résultat.
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
