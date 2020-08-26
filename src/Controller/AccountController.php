<?php

namespace App\Controller;

use App\Entity\ApiToken;
use App\Entity\ContactEmailAddress;
use App\Entity\User;
use App\Form\ApiTokenType;
use App\Form\ContactEmailAddressType;
use App\Form\RegiFormUserType;
use App\Repository\ApiTokenRepository;
use App\Repository\ContactEmailAddressRepository;
use App\Repository\UserRepository;
use App\Service\JwtService;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use phpDocumentor\Reflection\DocBlock\Serializer;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @IsGranted("ROLE_USER")
 */
class AccountController extends AbstractController
{
    private LoggerInterface $logger;
    /** @var Serializer */
    private $serializer;
    private UserRepository $userRepository;
    private ApiTokenRepository $apiTokenRepository;
    private UserPasswordEncoderInterface $userPasswordEncoder;
    private JwtService $jwtService;
    private ContactEmailAddressRepository $contactEmailAddressRepository;

    /**
     * AccountController constructor.
     * @param LoggerInterface $logger
     * @param SerializerInterface $serializer
     * @param UserRepository $userRepository
     * @param ApiTokenRepository $apiTokenRepository
     * @param UserPasswordEncoderInterface $userPasswordEncoder
     * @param JwtService $jwtService
     * @param ContactEmailAddressRepository $contactEmailAddressRepository
     */
    public function __construct(
        LoggerInterface $logger,
        SerializerInterface $serializer,
        UserRepository $userRepository,
        ApiTokenRepository $apiTokenRepository,
        UserPasswordEncoderInterface $userPasswordEncoder,
        JwtService $jwtService,
        ContactEmailAddressRepository $contactEmailAddressRepository
    ) {
        $this->logger = $logger;
        $this->serializer = $serializer;
        $this->userRepository = $userRepository;
        $this->apiTokenRepository = $apiTokenRepository;
        $this->userPasswordEncoder = $userPasswordEncoder;
        $this->jwtService = $jwtService;
        $this->contactEmailAddressRepository = $contactEmailAddressRepository;
    }

    /**
     * @Route("/account", name="app_account")
     * @param Request $request
     * @return Response
     * @throws ORMException
     */
    public function index(Request $request)
    {
        /** @var User $user */
        $user = $this->getUser();

        // Make sure each user has a apiToken
        if ($user->getApiToken() === null) {
            $apiToken = new ApiToken($user, $this->jwtService);
            $user->setApiToken($apiToken);
            $this->apiTokenRepository->saveApiToken($apiToken);
        }

        $apiTokenForm = $this->createForm(ApiTokenType::class, $user->getApiToken());
        $apiTokenForm->handleRequest($request);
        if ($apiTokenForm->isSubmitted() && $apiTokenForm->isValid()) {
            $apiToken = $user->getApiToken();
            $apiToken->renewToken();

            // Create ApiTokenForm again to have the updated expiredAt in it
            $apiTokenForm = $this->createForm(ApiTokenType::class, $user->getApiToken());

            $this->apiTokenRepository->saveApiToken($apiToken);

            $this->addFlash('renew-api-token=success', 'Je api-token is met een maand verlengd: lekker!!');

        }

        $userForm = $this->createForm(RegiFormUserType::class, $user);
        $userForm->handleRequest($request);
        if ($userForm->isSubmitted() && $userForm->isValid()) {
            $user = $userForm->getData();

            $passwordHashed = $this->userPasswordEncoder->encodePassword($user, $request->request->get('regi_form_user')['password']);
            $user->setPassword($passwordHashed);

            $this->userRepository->save($user);

            $this->addFlash('success-edit', 'Je gegevens zijn aangepast');
        }

        return $this->render('account/mydetails.html.twig', [
            'user' => $user,
            'userForm' => $userForm->createView(),
            'apiTokenForm' => $apiTokenForm->createView()
        ]);
    }

    /**
     * @Route("/show/contacts", name="app_show_contacts")
     */
    public function showContacts()
    {
        $user = $this->getUser();
        $existingContactEmailAddresses = $this->contactEmailAddressRepository->findBy(['user' => $user]);

        return $this->render('contact/show.contacts.html.twig', [
            'existingContactEmailAddresses' => $existingContactEmailAddresses
        ]);
    }

    /**
     * @Route("/add/contactemailaddress", name="app_add_contactemailaddress")
     * @param Request $request
     * @return Response
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function addContactEmailAddress(Request $request)
    {
        $contactEmailAddressAdded = false;
        $form = $this->createForm(ContactEmailAddressType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $existingContactEmailAddress = $this->contactEmailAddressRepository->findOneBy(['email' => $request->request->get('contact_email_address')['email']]);
            if ($existingContactEmailAddress === null) {
                $contactEmailAddress = ContactEmailAddress::create($request, $this->getUser());
                $this->contactEmailAddressRepository->save($contactEmailAddress);
                $contactEmailAddressAdded = true;
                $this->addFlash('contact-email-address-added', 'The email address is added to your contactlist');


            } else {
                $this->addFlash('duplicated-email-error', 'The email address is allready present in your contactlist');
            }
        }

        return $this->render('add/add.contact.email.address.html.twig', [
            'form' => $form->createView(),
            'contactEmailAddressAdded' => $contactEmailAddressAdded
        ]);
    }

    /**
     * @Route("/edit/contactemailaddress/{id}", name="app_edit_contactemailaddress")
     * @param $id
     * @param Request $request
     * @return Response
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function editContactEmailAddress($id, Request $request)
    {
        $existingContactEmailAddress = $this->contactEmailAddressRepository->find($id);
        $contactEmailAddressEdited = false;
        $errorSaveDoubleContactEmailAddress = false;
        $form = $this->createForm(ContactEmailAddressType::class, $existingContactEmailAddress);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $emailAllreadyExist = $this->contactEmailAddressRepository->findOneBy(['email'=>$request->request->get('contact_email_address')['email']]);
            if ($emailAllreadyExist !== null) {
                $errorSaveDoubleContactEmailAddress = true;
                $this->addFlash('duplicated-email-error', 'The email address is allready present in your contactlist');
            } else {
                $existingContactEmailAddress->setEmail($request->request->get('contact_email_address')['email']);
                $this->contactEmailAddressRepository->save($existingContactEmailAddress);
                $this->addFlash('email-edited-message', 'Added the email address to your contact list');


            }
        }

        return $this->render('contact/edit.contact.email.address.html.twig', [
            'form' => $form->createView(),
            'contactEmailAddressEdited' => $contactEmailAddressEdited,
            'errorSaveDoubleContactEmailAddress' => $errorSaveDoubleContactEmailAddress,
        ]);
    }

    /**
     * @Route("/contact/delete/{id}", name="app_delete_contact")
     * @param $id
     * @param Request $request
     * @return RedirectResponse
     */
    public function deleteContactEmailAddress($id, Request $request)
    {
        $deleteContactEmailAddress = $this->contactEmailAddressRepository->find($id);
        $this->contactEmailAddressRepository->delete($deleteContactEmailAddress);

        return $this->redirectToRoute('app_show_contacts');
    }
}
