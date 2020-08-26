<?php

namespace App\Repository;

use App\Entity\ApiToken;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

class ApiTokenRepository extends ServiceEntityRepository
{
    /**
     * ApiTokenRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ApiToken::class);
    }

    /**
     * @param $token
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function saveApiToken($token)
    {
        $this->_em->persist($token);
        $this->_em->flush();
    }
}
