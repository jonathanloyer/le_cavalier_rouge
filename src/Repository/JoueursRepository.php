<?php

namespace App\Repository;

use App\Entity\Joueurs;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Joueurs>
 */
class JoueursRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        // Je configure le dépôt pour gérer l'entité Joueurs.
        parent::__construct($registry, Joueurs::class);
    }

    function save(Joueurs $newPlayers, ?bool $flush=false)
    {
        // Je sauvegarde un nouvel objet Joueurs dans la base de données.
        $this->getEntityManager()->persist($newPlayers);
        if ($flush) {
            // J'effectue un flush pour valider les modifications dans la base de données si demandé.
            $this->getEntityManager()->flush();
        }
        // Je retourne le joueur sauvegardé.
        return $newPlayers;
    }

    //    /**
    //     * @return Joueurs[] Returns an array of Joueurs objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        // Je retourne une liste d'objets Joueurs filtrés selon une condition spécifique.
    //        return $this->createQueryBuilder('j')
    //            ->andWhere('j.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('j.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Joueurs
    //    {
    //        // Je retourne un joueur correspondant à une condition spécifique ou null si aucun résultat.
    //        return $this->createQueryBuilder('j')
    //            ->andWhere('j.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
