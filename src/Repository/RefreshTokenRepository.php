<?php

namespace App\Repository;

use App\Entity\RefreshToken;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method RefreshToken|null find($id, $lockMode = null, $lockVersion = null)
 * @method RefreshToken|null findOneBy(array $criteria, array $orderBy = null)
 * @method RefreshToken[]    findAll()
 * @method RefreshToken[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RefreshTokenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RefreshToken::class);
    }

    /**
     * @param array $refreshTokenData
     * @param $loggedInUserId
     * @return RefreshToken
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function saveRefreshToken(array $refreshTokenData, $loggedInUserId)
    {
        $datatime = new DateTime($refreshTokenData['expires']['date']);

        $refreshToken = new RefreshToken();
        $refreshToken->setRemoteUserId($refreshTokenData['targetUserId']);
        $refreshToken->setExpires($datatime);
        $refreshToken->setRefreshToken($refreshTokenData['refreshToken']);
        $refreshToken->setScope($refreshTokenData['scope']);
        $refreshToken->setLocalUserId($loggedInUserId);

        $this->_em->persist($refreshToken);
        $this->_em->flush();

        return $refreshToken;
    }
}
