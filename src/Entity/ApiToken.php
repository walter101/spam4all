<?php

namespace App\Entity;

use App\Service\JwtService;
use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use Ramsey\Uuid\Uuid;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ApiTokenRepository")
 */
class ApiToken
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private string $token;

    /**
     * @ORM\Column(type="datetime")
     */
    private DateTimeInterface $expiresAt;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="apiTokens", fetch="EAGER")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="boolean", nullable=true, options={"default" : 0})
     */
    private bool $active;

    /**
     * ApiToken constructor.
     * @param User $user
     * @param JwtService $jwtService
     */
    public function __construct(User $user, JwtService $jwtService)
    {
        try {
            $config = $jwtService->getConfig();
            $tokenUuid = Uuid::uuid4();
            $claims = [
                'email' => $user->getEmail(),
            ];
            $this->token = $jwtService->createToken(
                $config,
                $tokenUuid,
                'https://www.movie.application.nl',
                'localhost:8000',
                '+1 year',
                $claims
            );
        } catch (Exception $e) {
        }
        $this->user = $user;
        $this->active = 0;
        $this->expiresAt = new DateTime('+15 days');
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function getExpiresAt(): ?DateTimeInterface
    {
        return $this->expiresAt;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function isExpired(): bool
    {
        return $this->getExpiresAt() <= new DateTime();
    }

    public function renewToken(): void
    {
        $datetime = new DateTime('now');
        $this->expiresAt = $datetime->modify('+1 month');
    }

    public function getActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }
}
