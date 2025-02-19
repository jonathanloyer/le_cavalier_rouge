<?php

namespace App\Repository;

use App\Entity\Competitions;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Competitions>
 */
class CompetitionsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        // Je configure le dépôt pour gérer l'entité Competitions.
        parent::__construct($registry, Competitions::class);
    }

    /**
     * ça me permet d'ajouter une nouvelle compétition dans la base de donnée
     * @param Competitions $newCompetitions
     * @param bool|null $flush=false
     * 
     * @return [type]
     */
    function save(Competitions $newCompetitions, ?bool $flush=false)
    {
        // Je sauvegarde un nouvel objet Competitions dans la base de données.
        $this->getEntityManager()->persist($newCompetitions);
        if ($flush) {
            // J'effectue un flush pour valider les modifications dans la base de données si demandé.
            $this->getEntityManager()->flush();
        }
        // Je retourne la compétition sauvegardée.
        return $newCompetitions;
    }

    //    /**
    //     * @return Competitions[] Returns an array of Competitions objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        // Je retourne une liste d'objets Competitions filtrés selon une condition spécifique.
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Competitions
    //    {
    //        // Je retourne une compétition correspondant à une condition spécifique ou null si aucun résultat.
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
