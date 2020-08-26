<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PersonRepository")
 */
class Person
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="string", length=20)
     * @Assert\NotBlank(message="Voornaam mag niet leeg zijn!")
     */
    private string $firstName;

    /**
     * @ORM\Column(type="string", length=25)
     * @Assert\NotBlank(message="Achternaam mag niet leeg zijn!")
     */
    private string $lastName;

    /**
     * @ORM\Column(type="string", length=50)
     * @Assert\NotBlank(message="Straatnaam mag niet leeg zijn!")
     */
    private string $streetName;

    /**
     * @ORM\Column(type="string", length=10)
     * @Assert\NotBlank(message="Huisnummer mag niet leeg zijn!")
     */
    private string $streetNumber;

    /**
     * @ORM\Column(type="string", length=10)
     * @Assert\NotBlank(message="Postcode mag niet leeg zijn!")
     */
    private string $zipcode;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\User", inversedBy="person", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getStreetName(): ?string
    {
        return $this->streetName;
    }

    public function setStreetName(string $streetName): self
    {
        $this->streetName = $streetName;

        return $this;
    }

    public function getStreetNumber(): ?string
    {
        return $this->streetNumber;
    }

    public function setStreetNumber(string $streetNumber): self
    {
        $this->streetNumber = $streetNumber;

        return $this;
    }

    public function getZipcode(): ?string
    {
        return $this->zipcode;
    }

    public function setZipcode(string $zipcode): self
    {
        $this->zipcode = $zipcode;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @param Request $request
     * @param User $user
     * @return Person
     */
    public static function createPerson(Request $request, User $user)
    {
        $person = new self();
        $person->setFirstName($request->request->get('firstname'));
        $person->setLastName($request->request->get('lastname'));
        $person->setStreetName($request->request->get('streetname'));
        $person->setStreetNumber($request->request->get('streetnumber'));
        $person->setZipcode($request->request->get('zipcode'));
        /** Add User to Person */
        $person->setUser($user);

        return $person;
    }
}
