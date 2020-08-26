<?php

namespace App\Service;

use App\Constraints\jwt\JwtValidator;
use App\Repository\ApiTokenRepository;
use Exception;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\Validation\InvalidToken;
use Lcobucci\JWT\Validator;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class JwtService
{
    private ParameterBagInterface $parameterBag;
    private Configuration $config;
    private ApiTokenRepository $apiTokenRepository;

    /**
     * JwtService constructor.
     * @param ParameterBagInterface $parameterBag
     * @param ApiTokenRepository $apiTokenRepository
     */
    public function __construct(
        ParameterBagInterface $parameterBag,
        ApiTokenRepository $apiTokenRepository
    ) {
        $this->parameterBag = $parameterBag;

        $privateKey = file_get_contents($this->parameterBag->get('kernel.project_dir') . '/config/jwt/private.pem');
        $publicKey = file_get_contents($this->parameterBag->get('kernel.project_dir') . '/config/jwt/public.pem');

        $this->config = Configuration::forAsymmetricSigner(
            new Sha256(),
            new Key($privateKey, 'testing'),
            new Key($publicKey)
        );
        $this->apiTokenRepository = $apiTokenRepository;
    }

    /**
     * @return Configuration
     */
    public function getConfig(): Configuration
    {
        return $this->config;
    }

    /**
     * @param Configuration $config
     * @param UuidInterface $uuid
     * @param string $issuedBy
     * @param string $permittedForHost
     * @param string $expiresAt
     * @param array $claims
     * @return Token
     */
    public function createToken(
      Configuration $config,
      UuidInterface $uuid,
      string $issuedBy,
      string $permittedForHost,
      string $expiresAt,
      array $claims
    ): Token
    {
        $now = new \DateTimeImmutable();
        $builder = $config->createBuilder()
            ->identifiedBy($uuid)
            ->issuedBy($issuedBy)
            ->permittedFor($permittedForHost)
            ->issuedAt($now)
            ->canOnlyBeUsedAfter($now->modify('+5 seconds'))
            ->expiresAt($now->modify($expiresAt));

        foreach ($claims as $key => $claim) {
            $builder->withClaim($key, $claim);
        }

        return $builder->getToken($config->getSigner(), $config->getSigningKey());
    }

    /**
     * @param Validator $validator
     */
    public function setValidator(Validator $validator)
    {
        $this->config->setValidator($validator);
    }

    /**
     * @param Token $token
     * @throws Exception
     */
    public function assertValidate(Token $token)
    {
        $constraints = $this->config->getValidationConstraints();

        try {
            $this->config->getValidator()->assert($token, ...$constraints);
        } catch (InvalidToken $e) {
            throw new InvalidToken('Jwt content is not valid');
        }
    }

    /**
     * @param Token $token
     * @throws Exception
     */
    public function validateToken(Token $token)
    {
        $constraints = $this->config->getValidationConstraints();

        if (!$this->config->getValidator()->validate($token, ...$constraints)) {
            throw new InvalidToken('Unable to validate Jwt.');
        }
    }

    /**
     * @param Request $request
     * @return Token
     * @throws Exception
     */
    public function validateRequest(Request $request): Token
    {
        $authorization = $request->headers->get('authorization');
        if (empty($authorization)) {
            throw new UnauthorizedHttpException('Request must have the Authorization in the header', 'Add the Authorization in the header');
        }

        $jwt = substr($authorization, 7);
        if (empty($jwt)) {
            throw new UnauthorizedHttpException('Request must contain an JWT', 'Request must contain an JWT');
        }

        $existingToken = $this->apiTokenRepository->findOneBy(['token' => $jwt]);
        if (!$existingToken) {
            throw new InvalidToken('Processing non existing JWT');
        }
        if ($existingToken->getActive() === false) {
            throw new InvalidToken('Token is inactive');
        }
        if ($existingToken->isExpired()) {
            throw new InvalidToken('Token is expired');
        }

        $config = $this->getConfig();
        $token = $config->getParser()->parse($jwt);
        $config->setValidator(new JwtValidator());
        $this->assertValidate($token);

        return $token;
    }

    /**
     * @param Token $token
     * @return string
     */
    public function getTokenString(Token $token): string
    {
        return $token->headers() . '.' . $token->claims() . '.' . $token->signature();
    }
}