<?php

namespace App\Repository;

use App\Entity\AuthorizationCode;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;

/**
 * @method AuthorizationCode|null find($id, $lockMode = null, $lockVersion = null)
 * @method AuthorizationCode|null findOneBy(array $criteria, array $orderBy = null)
 * @method AuthorizationCode[]    findAll()
 * @method AuthorizationCode[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AuthorizationCodeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AuthorizationCode::class);
    }

    /**
     * @param Request $request
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function saveAuthorizationCode(Request $request)
    {
        $datetime = new DateTime();
        $datetime->setTimestamp($request->query->getAlnum('expires'));
        $authorizationcode = new AuthorizationCode();
        $authorizationcode->setExpires($datetime);
        $authorizationcode->setAuthorizationCode($request->query->get('authorizationCode'));
        $authorizationcode->setAuthorizedUserId($request->query->get('authorizedUserId'));
        $this->_em->persist($authorizationcode);
        $this->_em->flush();
    }
}
