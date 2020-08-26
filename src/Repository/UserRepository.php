<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

class UserRepository extends ServiceEntityRepository
{
    /**
     * UserRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * @return int|mixed|string
     */
    public function findAllAlphabeticaly()
    {
        return $this->createQueryBuilder('u')
            ->orderBy('u.email', 'ASC')
            ->getQuery()
            ->execute();
    }

    /**
     * @param $user
     * @throws \Doctrine\ORM\ORMException
     */
    public function save($user)
    {
        $this->_em->persist($user);
        $this->_em->flush();
    }

    /**
     * @param User $user
     * @return int|mixed|string|null
     * @throws NonUniqueResultException
     */
    public function IsUsernameTaken($user)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.email = :username')
            ->setParameter('username', $user->getEmail())
            ->getQuery()
            ->getOneOrNullResult();
    }
}
