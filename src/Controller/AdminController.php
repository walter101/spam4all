<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class AdminController extends AbstractController
{
    private $client;
    private ParameterBagInterface $parameterBag;

    /**
     * AdminController constructor.
     * @param ParameterBagInterface $parameterBag
     */
    public function __construct(
        ParameterBagInterface $parameterBag
    ) {
        $this->client = HttpClient::create();
        $this->parameterBag = $parameterBag;
    }

    /**
     * @Route("/register-as-client", name="register-as-client")
     *
     * A link that will send a GET request to register the client Spam4U as a known Client at the authorization server Xmail
     *
     * @throws TransportExceptionInterface
     */
    public function registerSpam4AllOnXmail()
    {
        $this->client = HttpClient::create([
            'headers' => [
                'Content-Type' => 'text/plain',
            ]
        ]);

        $response = $this->client->request(
            'GET',
            'http://host.docker.internal:8620/client-register',
            [
                'query' => [
                    'email' => $this->parameterBag->get('spam4all_email'),
                    'firstname' => 'spammer',
                    'password' => $this->parameterBag->get('spam4all_password'),
                    'username' => $this->parameterBag->get('spam4all_username'),
                    'scope' => 'emailcontacts',
                    'secret' => $this->parameterBag->get('spam4all_secret'),
                    'redirect_uri' => $this->parameterBag->get('spam4all_redirect_uri'),
                    'grant-type' => 'authorization_code',
                    'client_id' => 12345
                ]
            ]
        );

        $response = $response->getContent();
        $response = json_decode($response, true);

        return $this->render('admin/admin.register.client.html.twig', [
            'action' => $response['action'],
            'jwt' => $response['jwt'],
            'remote_user_id' => $response['user_id'],
            'client_user_id' => $response['client_user_id']
        ]);
    }
}