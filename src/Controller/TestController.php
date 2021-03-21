<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TestController extends AbstractController
{
    private $client;
    /**
     * @var ParameterBagInterface
     */
    private ParameterBagInterface $parameterBag;

    /**
     * TestController constructor.
     * @param ParameterBagInterface $parameterBag
     */
    public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->parameterBag = $parameterBag;
    }

    /**
     * @Route("/testwp", name="testwp", methods={"GET","POST"})
     */
    public function testwp()
    {
        $test = $this->getParameter('kernel.project_dir');

        $test2 = scandir($test);
        return new Response('ok');
        return new JsonResponse('OK' . $test);//
    }

    /**
     * @Route("testPost")
     */
    public function testPostCall()
    {
        $this->client = HttpClient::create([
            'headers' => [
                'Content-Type' => 'text/plain',
                'Authorization' => 'auth'
            ]
        ]);

        $data = json_encode(
            [
                'een' => 1,
                'twee' => 2
            ]
        );

        $response = $this->client->request(
            'POST',
            'http://host.docker.internal:8620/test',
            [
                'query' => [
                    'email' => $this->parameterBag->get('spam4all_email'),
                    'password' => $this->parameterBag->get('spam4all_password'),
                    'username' => $this->parameterBag->get('spam4all_username'),
                    'secret' => $this->parameterBag->get('spam4all_secret'),
                ],
                'body' => $data
            ]
        );


//        $this->client->request('GET', 'http://host.docker.internal:8620/test',
//            [
//                'query' => [
//                    'drie' => 3,
//                    'vier' => 4
//                ]
//            ]
//        );

        $values = json_decode($response->getContent());
        dd($values);
        return new Response($response);
        die('Result van een postCall');
    }
}