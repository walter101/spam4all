<?php

namespace App\Controller;

use App\Repository\ContactEmailAddressRepository;
use App\Repository\RefreshTokenRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class FrontendController extends AbstractController
{
    private RefreshTokenRepository $refreshTokenRepository;

    /**
     * FrontendController constructor.
     * @param RefreshTokenRepository $refreshTokenRepository
     */
    public function __construct(
        RefreshTokenRepository $refreshTokenRepository
    ) {
        $this->refreshTokenRepository = $refreshTokenRepository;
    }

    /**
     * @Route("/", name="app_index")
     */
    public function index()
    {
        $user = $this->getUser();
        $refreshToken = null;

        // Check if logged in user already granted us access to fetch his email contacts at Xmail
        if ($user !== null) {
            $refreshToken = $this->refreshTokenRepository->findOneBy(['localUserId' => $user->getId()], ['id' => 'DESC']);
        }

        return $this->render(
            'index.html.twig',
            [
                'user' => $user,
                'refreshToken' => $refreshToken
            ]);
    }
}