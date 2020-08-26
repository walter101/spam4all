<?php

namespace App\Repository;

use App\dto\PersonDto;
use App\Entity\Person;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PersonRepository extends ServiceEntityRepository
{
    /**
     * PersonRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Person::class);
    }

    /**
     * @param PersonDto $personDto
     */
    public function updatePersonFromApiCall(PersonDto $personDto)
    {
        $this->createQueryBuilder('p')
            ->update()
            ->set('p.firstName', ':firstName')
            ->set('p.lastName', ':lastName')
            ->set('p.streetName', ':streetName')
            ->set('p.streetNumber', ':streetNumber')
            ->set('p.zipcode', ':zipcode')
            ->where('p.user = ?2')
            ->setParameter(':firstName', $personDto->getFirstName())
            ->setParameter(':lastName', $personDto->getLastName())
            ->setParameter(':streetName', $personDto->getStreetName())
            ->setParameter(':streetNumber', $personDto->getStreetNumber())
            ->setParameter(':zipcode', $personDto->getZipcode())
            ->setParameter(2, $personDto->getId())
            ->getQuery()
            ->execute();
    }
}
