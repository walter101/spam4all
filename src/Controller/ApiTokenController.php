<?php

namespace App\Controller;

use App\Constraints\jwt\JwtMustContainUserId;
use App\Constraints\jwt\JwtValidator;
use App\Repository\ApiTokenRepository;
use App\Service\JwtService;
use Exception;
use Lcobucci\JWT\Validation\InvalidToken;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\TokenNotFoundException;

class ApiTokenController extends AbstractController
{
    /**
     * Als app niet omvalt deze hele ApiTokenController verwijderen, wordt niet gebruikt
     */

//    private JwtService $jwtService;
//    private ApiTokenRepository $apiTokenRepository;
//
//    /**
//     * ApiTokenController constructor.
//     * @param JwtService $jwtService
//     * @param ApiTokenRepository $apiTokenRepository
//     */
//    public function __construct(JwtService $jwtService, ApiTokenRepository $apiTokenRepository)
//    {
//        $this->jwtService = $jwtService;
//        $this->apiTokenRepository = $apiTokenRepository;
//    }
//
//    /**
//     * Postman: select type: Bearer Token, past the JWT
//     * Api: authorize with just the Jwt and try the calls
//     *
//     * Api / Jwt test url
//     * @Route("/checkjwt")
//     * @param Request $request
//     * @return JsonResponse
//     * @throws Exception
//     */
//    public function checkJwt(Request $request)
//    {
//        $authorization = substr($request->headers->get('authorization'), 7);
//        if (empty($authorization)) {
//            throw new UnauthorizedHttpException('Request must have the authorization header', 'Authorization header is missing');
//        }
//
//        $config = $this->jwtService->getConfig();
//
//        $token = $config->getParser()->parse($authorization);
//
//        $config->setValidator(new JwtValidator());
//        $this->jwtService->assertValidate($token);
//
//        $existingToken = $this->apiTokenRepository->findOneBy(['token' => $authorization]);
//        if (!$existingToken) {
//            throw new TokenNotFoundException('Processing non existing JWT');
//        }
//        if ($existingToken->getActive() === false) {
//            throw new InvalidToken('Token is inactive');
//        }
//        if ($existingToken->isExpired()) {
//            throw new InvalidToken('Token is expired');
//        }
//
//        return new JsonResponse('Jwt is valid');
//    }
//
//    /**
//     * Dit is een jqqt test route
//     * @Route("/newToken")
//     * @throws Exception
//     */
//    public function newToken(Request $request)
//    {
//        $payload = json_decode($request->getContent());
//
//        $config = $this->jwtService->getConfig();
//
//        $tokenUuid = Uuid::uuid4();
//        $claims = [
//            'username' => $payload->username,
//            'userId' => $payload->userId,
//        ];
//        $token = $this->jwtService->createToken(
//            $config,
//            $tokenUuid,
//            'https://www.movie.application.nl',
//            'localhost:8000',
//            '+1 year',
//            $claims
//        );
//
//        // Validate
//        $config->setValidator(new JwtMustContainUserId());
//        $this->jwtService->assertValidate($token);
//        $this->jwtService->validateToken($token);
//
//        $tokenString = $this->jwtService->getTokenString($token);
//
//        return new JsonResponse($tokenString);
//    }
}