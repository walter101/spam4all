<?php

namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class LoginFormAuthenticator extends AbstractFormLoginAuthenticator
{
    /** Used to send anonymous user back to the page he came from after logging in */
    use TargetPathTrait;

    private UserRepository $userRepository;
    private RouterInterface $router;
    private UserPasswordEncoderInterface $passwordEncoder;

    /**
     * LoginFormAuthenticator constructor.
     * @param UserRepository $userRepository
     * @param RouterInterface $router
     * @param UserPasswordEncoderInterface $passwordEncoder
     */
    public function __construct(
        UserRepository $userRepository,
        RouterInterface $router,
        UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->userRepository = $userRepository;
        $this->router = $router;
        $this->passwordEncoder = $passwordEncoder;
    }

    /**
     * Check if this authenticator should run or not during this request
     *
     * @param Request $request
     * @return bool
     */
    public function supports(Request $request)
    {
        return $request->attributes->get('_route') === 'app_login'
            && $request->isMethod('POST');
    }

    /**
     * Get the credentials from the request
     *
     * @param Request $request
     * @return array|mixed
     */
    public function getCredentials(Request $request)
    {
        $credentials = [
            'email' => $request->request->get('login_form')['email'],
            'password' => $request->request->get('login_form')['password']
        ];

        /** Save the last username value in session so its printed back again in the form if an error occurs */
        $request->getSession()->set(
            Security::LAST_USERNAME,
            $credentials['email']
        );

        return $credentials;
    }

    /**
     * Find a User in the database
     * If this fails onAuthenticationFails will be called an user will be redirected to the getLoginUrl() -> login url
     *
     * @param mixed $credentials
     * @param UserProviderInterface $userProvider
     * @return User
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        /** @var User $user */
        $user = $this->userRepository->findOneBy(['email' => $credentials['email']]);
        return $user;
    }

    /**
     * Return true if credentials matched the user object
     * Or true if this was an api call
     *
     * @param mixed $credentials
     * @param UserInterface $user
     * @return bool
     */
    public function checkCredentials($credentials, UserInterface $user)
    {
        return $this->passwordEncoder->isPasswordValid($user, $credentials['password']);
    }

    /**
     * Redirect to a page after successful login
     *
     * @param Request $request
     * @param TokenInterface $token
     * @param string $providerKey
     * @return RedirectResponse
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        /** If targetPath was stored in session send user back to the page he came from after loggin in */
        if ($targetPath = $this->getTargetPath($request->getSession(), $providerKey)) {
            die($targetPath);
            return new RedirectResponse($targetPath);
        }

        return new RedirectResponse($this->router->generate('app_index'));
    }

    /**
     * If authenticating fails ($this->getUser was not able to find a user with provided logins)
     * then onAuthenticationFail will call this method for a redirect url
     * @return string
     */
    public function getLoginUrl()
    {
        return $this->router->generate('app_login');
    }
}
