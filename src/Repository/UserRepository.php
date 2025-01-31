<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        // Je configure le dépôt pour gérer l'entité User.
        parent::__construct($registry, User::class);
    }

    function save(User $newUser, ?bool $flush=false)
    {
        // Je sauvegarde un nouvel utilisateur dans la base de données.
        $this->getEntityManager()->persist($newUser);
        if ($flush){
            // Je valide les modifications dans la base de données si flush est activé.
            $this->getEntityManager()->flush();
        }
        return $newUser; // Je retourne l'utilisateur sauvegardé.
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        // Je vérifie si l'utilisateur est bien une instance de User.
        if (!$user instanceof User) {
            // Je lance une exception si ce n'est pas le cas.
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        // Je mets à jour le mot de passe de l'utilisateur avec le nouveau hashé.
        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush(); // Je sauvegarde les changements dans la base de données.
    }

    //    /**
    //     * @return User[] Returns an array of User objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        // Je retourne une liste d'utilisateurs filtrés par un champ spécifique.
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('u.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?User
    //    {
    //        // Je retourne un utilisateur correspondant à une condition spécifique ou null si aucun résultat.
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
