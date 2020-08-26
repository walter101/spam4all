<?php

namespace App\Entity;

use Assert\Assert;
use Assert\Assertion;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\Request;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ContactEmailAddressRepository")
 */
class ContactEmailAddress
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=150)
     */
    private $email;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="contactEmailAddresses")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public static function create(Request $request, $user)
    {
        Assertion::string($request->request->get('contact_email_address')['email']);
        Assertion::isInstanceOf($user, User::class);

        $contactEmailAddress = new self();
        $contactEmailAddress->setEmail($request->request->get('contact_email_address')['email']);
        $contactEmailAddress->setUser($user);
        return $contactEmailAddress;
    }
}
