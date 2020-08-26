<?php

namespace App\Controller;

use App\Entity\ApiToken;
use App\Entity\Person;
use App\Entity\User;
use App\Form\LoginFormType;
use App\Repository\UserRepository;
use App\Service\JwtService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * Class SecurityController
 */
class SecurityController extends AbstractController
{
    private AuthenticationUtils $authenticationUtils;
    private CsrfTokenManagerInterface $csrfTokenManager;
    private UserPasswordEncoderInterface $passwordEncoder;
    private EntityManagerInterface $entityManager;
    private UserRepository $userRepository;
    private JwtService $jwtService;

    /**
     * SecurityController constructor.
     * @param AuthenticationUtils $authenticationUtils
     * @param CsrfTokenManagerInterface $csrfTokenManager
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param EntityManagerInterface $entityManager
     * @param UserRepository $userRepository
     * @param JwtService $jwtService
     */
    public function __construct(
        AuthenticationUtils $authenticationUtils,
        CsrfTokenManagerInterface $csrfTokenManager,
        UserPasswordEncoderInterface $passwordEncoder,
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
        JwtService $jwtService
    )
    {
        $this->authenticationUtils = $authenticationUtils;
        $this->csrfTokenManager = $csrfTokenManager;
        $this->passwordEncoder = $passwordEncoder;
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
        $this->jwtService = $jwtService;
    }

    /**
     * @Route("/regi", name="app_register")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function register(Request $request)
    {

        $formValues = [];
        $userExists = false;
        $registrationState = false;

        $csrfToken = new CsrfToken('loginform', $request->request->get('_csrf_token'));
        if ($request->getMethod()==='POST') {
            $formValues = [
                'firstname' => $request->request->get('firstname'),
                'lastname' => $request->request->get('lastname'),
                'streetname' => $request->request->get('streetname'),
                'streetnumber' => $request->request->get('streetnumber'),
                'zipcode' => $request->request->get('zipcode'),
                'email' => $request->request->get('email'),
                'password' => $request->request->get('password'),
            ];

            if (!$this->csrfTokenManager->isTokenValid($csrfToken)) {
                throw new Exception('Form is not valid, someone messed with it');
            }

            $existingUser = $this->userRepository->findOneBy(['email' => $request->request->get('email')]);
            if ($existingUser === null) {
                $user = User::createUser($request, $this->passwordEncoder);
                $person = Person::createPerson($request, $user);
                $apiToken = new ApiToken($user, $this->jwtService);

                $this->entityManager->persist($user);
                $this->entityManager->persist($person);
                $this->entityManager->persist($apiToken);
                $this->entityManager->flush();

                $registrationState = 'succes';
            } else {
                $userExists = true;
            }
        }

        return $this->render('security/register.html.twig', [
            'userExists' => $userExists,
            'formValues' => $formValues,
            'registrationState' => $registrationState
        ]);
    }

    /**
     * @Route("/login", name="app_login")
     * @return Response
     */
    public function login()
    {
        $form = $this->createForm(LoginFormType::class);

        return $this->render('security/login2.html.twig', [
            'userForm' => $form->createView()
        ]);
    }

    /**
     * Route needed for symfony to be able to logout
     * It does not do anything
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {

    }
}
