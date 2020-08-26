<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class ClientJwtService
{
    private ParameterBagInterface $parameterBag;

    /**
     * RefreshTokenService constructor.
     * @param ParameterBagInterface $parameterBag
     */
    public function __construct(
        ParameterBagInterface $parameterBag
    ) {
        $this->parameterBag = $parameterBag;
        $this->client = HttpClient::create(
            [
                'headers' => [
                    'Content-Type' => 'text/plain',
                    'Authorization' => 'someStringAsToken'
                ]
            ]
        );
    }

    /**
     * Perform a GET request to Xmail fetching the clients JWT
     * @throws TransportExceptionInterface
     */
    public function fetchClientJwt()
    {
        $clientJwt = $this->client->request(
            'GET',
            'http://host.docker.internal:8620/fetch-jwt-by-user-details',
            [
                'query' => [
                    'username' => $this->parameterBag->get('spam4all_username'),
                    'email' => $this->parameterBag->get('spam4all_email'),
                    'password' => $this->parameterBag->get('spam4all_password'),
                    'secret' => $this->parameterBag->get('spam4all_secret')
                ]
            ]
        );

        return $clientJwt;
    }
}