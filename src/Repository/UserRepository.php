<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(User $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(User $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newHashedPassword);
        $this->_em->persist($user);
        $this->_em->flush();
    }

    public function search($prenom, $nom)
    {
        $query = $this->createQueryBuilder('u');
        // all users
        if ((null == $prenom) && (null == $nom)) {
            $query->select('u');

            return $query->getQuery()->getResult();
        }
        // user with prenom and nom and demande envoyé
        if ((null != $prenom) && (null != $nom)) {
            $query->select('u')
            ->Where('MATCH_AGAINST(u.prenom) AGAINST(:prenom boolean)>0')
            ->Where('MATCH_AGAINST(u.nom) AGAINST(:nom boolean)>0')
            ->setParameters(new ArrayCollection([
                new Parameter('prenom', '*'.$prenom.'*'),
                new Parameter('nom', '*'.$nom.'*'),
            ]));

            return $query->getQuery()->getResult();
        }

        if ((null != $prenom) && (null == $nom)) {
            $query->select('u')
            ->Where('MATCH_AGAINST(u.prenom) AGAINST(:prenom boolean)>0')
            ->setParameters(new ArrayCollection([
                new Parameter('prenom', '*'.$prenom.'*'),
            ]));

            return $query->getQuery()->getResult();
        }
        if ((null == $prenom) && (null != $nom)) {
            $query->select('u')
            ->Where('MATCH_AGAINST(u.nom) AGAINST(:nom boolean)>0')
            ->setParameters(new ArrayCollection([
                new Parameter('nom', '*'.$nom.'*'),
            ]));

            return $query->getQuery()->getResult();
        }
    }
    // /**
    //  * @return User[] Returns an array of User objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
