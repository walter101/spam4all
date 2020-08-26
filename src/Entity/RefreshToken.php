<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\RefreshTokenRepository")
 */
class RefreshToken
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $remote_user_id;

    /**
     * @ORM\Column(type="text")
     */
    private $refreshToken;

    /**
     * @ORM\Column(type="datetime")
     */
    private $expires;

    /**
     * @ORM\Column(type="integer")
     */
    private $localUserId;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $scope;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRemoteUserId(): ?int
    {
        return $this->remote_user_id;
    }

    public function setRemoteUserId(int $remote_user_id): self
    {
        $this->remote_user_id = $remote_user_id;

        return $this;
    }

    public function getRefreshToken(): ?string
    {
        return $this->refreshToken;
    }

    public function setRefreshToken(string $refreshToken): self
    {
        $this->refreshToken = $refreshToken;

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

    public function getLocalUserId(): ?int
    {
        return $this->localUserId;
    }

    public function setLocalUserId(int $localUserId): self
    {
        $this->localUserId = $localUserId;

        return $this;
    }

    public function getScope(): ?string
    {
        return $this->scope;
    }

    public function setScope(string $scope): self
    {
        $this->scope = $scope;

        return $this;
    }
}
