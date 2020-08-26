<?php

namespace App\Service;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class ClientService extends AbstractController
{
    /** @var HttpClient */
    private $client;
    /** @var bool envar IS_API_CALL_ACTIVE */
    private $isApiCallActive;

    /**
     * Client constructor.
     * @param bool $isApiCallActive
     */
    public function __construct(bool $isApiCallActive)
    {
        $this->isApiCallActive = $isApiCallActive;
        return $this->client = HttpClient::create();
    }

    /**
     * @param string $url
     * @return ResponseInterface|null
     * @throws TransportExceptionInterface
     */
    public function getResponse($url)
    {
        if ($this->isApiCallActive()) {
            return $this->client->request('GET', $url);
        }
        return null;
    }

    /**
     * Check if api calls are set active (envar setting)
     * @return bool
     */
    public function isApiCallActive(): bool
    {
        return $this->isApiCallActive === true ? true : false;
    }
}