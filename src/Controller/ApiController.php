<?php

namespace App\Controller;

use App\dto\PersonDto;
use App\Repository\PersonRepository;
use App\Service\JwtService;
use Assert\AssertionFailedException;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ApiController extends AbstractController
{
    /**
     * Als app niet omvlat deze hele ApiController verwijderen, die wordt hier niet gebruikt
     */
//    private JwtService $jwtService;
//    private PersonRepository $personRepository;
//
//    /**
//     * ApiController constructor.
//     * @param JwtService $jwtService
//     * @param PersonRepository $personRepository
//     */
//    public function __construct(
//        JwtService $jwtService,
//        PersonRepository $personRepository
//    )
//    {
//        $this->jwtService = $jwtService;
//        $this->personRepository = $personRepository;
//    }
//
//    /**
//     * @Route("/user")
//     * @param Request $request
//     * @return JsonResponse
//     * @throws AssertionFailedException
//     * @throws Exception
//     */
//    public function updateUserDetails(Request $request)
//    {
//        $this->jwtService->validateRequest($request);
//
//        $payload = json_decode($request->getContent());
//
//        $personDto = PersonDto::create($payload);
//
//        $this->personRepository->updatePersonFromApiCall($personDto);
//
//        $json = [
//            'message' => 'well done!, you updated your personal details!'
//        ];
//
//        return new JsonResponse($json);
//    }
}