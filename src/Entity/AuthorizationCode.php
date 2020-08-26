<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AuthorizationCodeRepository")
 */
class AuthorizationCode
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     */
    private $authorizationCode;

    /**
     * @ORM\Column(type="integer")
     */
    private $authorizedUserId;

    /**
     * @ORM\Column(type="datetime")
     */
    private $expires;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAuthorizationCode(): ?string
    {
        return $this->authorizationCode;
    }

    public function setAuthorizationCode(string $authorizationCode): self
    {
        $this->authorizationCode = $authorizationCode;

        return $this;
    }

    public function getAuthorizedUserId(): ?int
    {
        return $this->authorizedUserId;
    }

    public function setAuthorizedUserId(int $authorizedUserId): self
    {
        $this->authorizedUserId = $authorizedUserId;

        return $this;
    }

    public function getExpires(): ?\DateTimeInterface
    {
        return $this->expires;
    }

    public function setExpires(\DateTimeInterface $expires): self
    {
        $this->expires = $expires;

        return $this;
    }
}
