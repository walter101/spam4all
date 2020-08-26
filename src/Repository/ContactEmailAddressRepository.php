<?php

namespace App\Repository;

use App\Entity\ContactEmailAddress;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ContactEmailAddress|null find($id, $lockMode = null, $lockVersion = null)
 * @method ContactEmailAddress|null findOneBy(array $criteria, array $orderBy = null)
 * @method ContactEmailAddress[]    findAll()
 * @method ContactEmailAddress[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ContactEmailAddressRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ContactEmailAddress::class);
    }

    /**
     * @param ContactEmailAddress $contactEmailAddress
     * @return bool
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function save(ContactEmailAddress $contactEmailAddress)
    {
        $insertedContactEmailAddresses = [];
        $existingContactEmailAddress = $this->findOneBy(['email' => $contactEmailAddress->getEmail()]);
        if ($existingContactEmailAddress === null) {
            $this->_em->persist($contactEmailAddress);
            $this->_em->flush();
            $insertedContactEmailAddresses[] = $contactEmailAddress;
            return  true;
        }

        return false;
    }

    public function delete(?ContactEmailAddress $deleteContactEmailAddress)
    {
        $this->_em->remove($deleteContactEmailAddress);
        $this->_em->flush();
    }
}
