<?php

namespace App\Controller;

use App\Entity\ContactEmailAddress;
use App\Repository\AuthorizationCodeRepository;
use App\Repository\ContactEmailAddressRepository;
use App\Repository\RefreshTokenRepository;
use App\Repository\UserRepository;
use App\Service\ClientJwtService;
use App\Service\RefreshTokenService;
use DateTime;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class OauthController extends AbstractController
{
    private $client;// todo: check which typehint we can use here and do that in all other occurrences to
    private RefreshTokenService $refreshTokenService;
    private ParameterBagInterface $parameterBag;
    private ClientJwtService $clientJwtService;
    private AuthorizationCodeRepository $authorizationCodeRepository;
    private SessionInterface $session;
    private RefreshTokenRepository $refreshTokenRepository;
    private ContactEmailAddressRepository $contactEmailAddressRepository;
    private UserRepository $userRepository;

    /**
     * OauthController constructor.
     * @param RefreshTokenService $refreshTokenService
     * @param ParameterBagInterface $parameterBag
     * @param ClientJwtService $clientJwtService
     * @param AuthorizationCodeRepository $authorizationCodeRepository
     * @param RefreshTokenRepository $refreshTokenRepository
     * @param ContactEmailAddressRepository $contactEmailAddressRepository
     * @param UserRepository $userRepository
     */
    public function __construct(
        RefreshTokenService $refreshTokenService,
        ParameterBagInterface $parameterBag,
        ClientJwtService $clientJwtService,
        AuthorizationCodeRepository $authorizationCodeRepository,
        RefreshTokenRepository $refreshTokenRepository,
        ContactEmailAddressRepository $contactEmailAddressRepository,
        UserRepository $userRepository
    ) {
        $this->refreshTokenService = $refreshTokenService;
        $this->parameterBag = $parameterBag;
        $this->clientJwtService = $clientJwtService;
        $this->authorizationCodeRepository = $authorizationCodeRepository;
        $this->refreshTokenRepository = $refreshTokenRepository;
        $this->parameterBag = $parameterBag;
        $this->client = HttpClient::create([
            'headers' => [
                'Content-Type' => 'text/plain',
                'Authorization' => 'someStringAsToken'
            ]
        ]);
        $this->contactEmailAddressRepository = $contactEmailAddressRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * Start client authorization
     * Redirect User to authorization server and ask for permission to access emailcontacts
     * Get a tempcode or a access denied return string
     *
     * @Route("/start-oauth-authorization-client-process", name="startOauthAuthorizationClientProcess")
     */
    public function startOauthProcess()
    {
        $url = 'http://host.docker.internal:8620/start-oauth-authorization-client-process?';
        $parametersData = [
            'response_type' => 'authorization_code',
            'client_user_id' => $this->parameterBag->get('spam4all_user_id'),
            'redirect_uri' => $this->parameterBag->get('spam4all_redirect_uri'),
            'scope' => 'emailcontacts',
            'state' => '12345-identifier-to-this-request',
            'target_user' => $this->getUser()->getId()
        ];

        $parameters = http_build_query($parametersData);
        return $this->redirect($url.$parameters);
    }

    /**
     * Redirect url back from authorization server here to the client
     * processing the provided authorization code and do a POST request with it to fetch an refreshToken
     *
     * @Route("/oauth-authorization-client-redirect-url", name="oauthAuthorizationClientRedirectUrl")
     * @param Request $request
     * @return Response
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function oauthResult(Request $request)
    {
        $authorizationStatus = false;
        $UUIDv4 = '/^[0-9A-F]{8}-[0-9A-F]{4}-4[0-9A-F]{3}-[89AB][0-9A-F]{3}-[0-9A-F]{12}$/i';
        if (preg_match($UUIDv4, $request->query->get('authorizationCode'))) {
            $authorizationStatus = true;
        }

        $loggedInUserId = $request->query->get('target_user');
        $authorizedUserId = $request->query->get('authorizedUserId');
        $authorizationCode = $request->query->get('authorizationCode');
        $expires = $request->query->get('expires');
        $expiresDateTime = new DateTime();
        $expiresDateTime->setTimestamp($expires);

        $this->authorizationCodeRepository->saveAuthorizationCode($request);

        // Use authorizationCode to fetch a refresh token
        $data = [
            'client_user_id' => $this->parameterBag->get('spam4all_user_id'),
            'authorizationCode' => $authorizationCode
        ];
        $refreshTokenResponse = $this->client->request(
            'POST',
            'http://host.docker.internal:8620/fetch-refresh-token',
            [
                'body' => json_encode($data)
            ]
        );

        $refreshToken = json_decode($refreshTokenResponse->getContent(), true);
        $refreshToken = $this->refreshTokenRepository->saveRefreshToken($refreshToken, $loggedInUserId);

        // Possible option: use refreshToken to fetch access token, and use that to access data on the Xmail server

        return $this->render('oauth/authorization.client.redirect.url.html.twig', [
            'authorizationCode' => $authorizationCode,
            'authorizedUserId' => $authorizedUserId,
            'authorizationStatus' => $authorizationStatus,
            'loggedInUserId' => $loggedInUserId
        ]);
    }

    /**
     * Only available if user is logged in
     * This route will fetch the email contacts for this logged in user at the 3th party (Xmail)
     * The email addresses will be stored in the user account here so the user can use them in his account at Spam4All
     *
     * @Route("/fetch-email-contacts", name="fetchEmailContacts")
     */
    public function fetchEmailContacts()
    {
        $insertedEmails = [];
        $jwtClientResponse = $this->clientJwtService->fetchClientJwt();
        $jwtClient = $jwtClientResponse->getContent();

        $this->client = HttpClient::create([
            'headers' => [
                'Content-Type' => 'text/plain',
                'Authorization' => $jwtClient
            ]
        ]);

        $refreshToken = $this->refreshTokenRepository->findOneBy(['localUserId' => $this->getUser()->getId()], ['id' => 'DESC']);

        if ($refreshToken === null) {
            throw new UnauthorizedHttpException('No RefreshToken available');
        }

        // Fetch the email contacts at Xmail
        $data = [
            'email' => $this->parameterBag->get('spam4all_email'),
            'password' => $this->parameterBag->get('spam4all_password'),
            'username' => $this->parameterBag->get('spam4all_username'),
            'secret' => $this->parameterBag->get('spam4all_secret'),
            'refreshToken' => $refreshToken->getRefreshToken()
        ];
        $response = $this->client->request(
            'POST',
            'http://host.docker.internal:8620/client-fetch-email-contacts',
            [
                'body' => json_encode($data)
            ]
        );

        $emailContacts = $response->getContent();
        $emailContacts = json_decode($emailContacts, true);

        $user = $this->userRepository->find($this->getUser()->getId());

        // Process the email contacts from 3th party to user account here
        foreach ($emailContacts as $emailContact) {
            $contactEmailAddress = new ContactEmailAddress();
            $contactEmailAddress->setUser($user);
            $contactEmailAddress->setEmail($emailContact);

            $contactEmailAddressInserted = $this->contactEmailAddressRepository->save($contactEmailAddress);

            if ($contactEmailAddressInserted) {
                $insertedEmails[] = $contactEmailAddress;
            }
        }

        return $this->render('oauth/result.fetch.contact.email.addresses.url.html.twig', [
            'insertedEmails' => $insertedEmails
        ]);
    }
}