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

    /**
     * Sauvegarde une nouvelle feuille de match
     */
    public function save(FeuilleMatch $newFeuilleMatch, ?bool $flush = false): FeuilleMatch
    {
        // Assurez-vous que joueurs est toujours un tableau valide
        if ($newFeuilleMatch->getJoueurs() === null) {
            $newFeuilleMatch->setJoueurs([]);
        }

        $this->getEntityManager()->persist($newFeuilleMatch);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
        return $newFeuilleMatch;
    }


    /**
     * Récupère les feuilles de match par type (criterium ou national)
     */
    public function findByType(string $type): array
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.type = :type')
            ->setParameter('type', $type)
            ->orderBy('f.creation', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère les feuilles de match selon plusieurs critères : type, groupe, interclub
     */
    public function findByCriteria(?string $type = null, ?string $groupe = null, ?string $interclub = null): array
    {
        $qb = $this->createQueryBuilder('f');

        if ($type) {
            $qb->andWhere('f.type = :type')
                ->setParameter('type', $type);
        }

        if ($groupe) {
            $qb->andWhere('f.groupe = :groupe')
                ->setParameter('groupe', $groupe);
        }

        if ($interclub) {
            $qb->andWhere('f.interclub = :interclub')
                ->setParameter('interclub', $interclub);
        }

        return $qb->orderBy('f.creation', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère les joueurs et résultats d'une feuille de match
     */
    public function getJoueursForMatch(FeuilleMatch $feuilleMatch): array
    {
        $joueursData = $feuilleMatch->getJoueurs() ?? [];

        return [
            'joueursA' => array_map(fn($data) => $data['joueurA'] ?? null, $joueursData),
            'resultats' => array_map(fn($data) => $data['resultat'] ?? null, $joueursData),
            'joueursB' => array_map(fn($data) => $data['joueurB'] ?? null, $joueursData),
        ];
    }

    /**
     * Récupère les feuilles de match récentes, triées par date de création
     */
    public function findRecent(int $limit = 10): array
    {
        return $this->createQueryBuilder('f')
            ->orderBy('f.creation', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
