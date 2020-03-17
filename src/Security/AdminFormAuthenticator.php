<?php

namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\PasswordAuthenticatedInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;

class AdminFormAuthenticator extends AbstractFormLoginAuthenticator implements PasswordAuthenticatedInterface
{
    use TargetPathTrait;

    private $userRepository;
    private $entityManager;
    private $urlGenerator;
    private $csrfTokenManager;
    private $passwordEncoder;

    public function __construct(UserRepository $userRepository, EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator, CsrfTokenManagerInterface $csrfTokenManager, UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->entityManager = $entityManager;
        $this->urlGenerator = $urlGenerator;
        $this->csrfTokenManager = $csrfTokenManager;
        $this->passwordEncoder = $passwordEncoder;

        $this->userRepository = $userRepository;
    }

    public function supports(Request $request)
    {
        // die('Our authenticator is alive!');    
        return 'admin_account_login' === $request->attributes->get('_route')
            && $request->isMethod('POST');
    }

    public function getCredentials(Request $request)
    {
        // dd($request->request->all());
        $credentials = [
            'email' => $request->request->get('_username'),
            'password' => $request->request->get('_password'),
            'csrf_token' => $request->request->get('_csrf_token'),
        ];
        // dd($credentials);
        // $request->getSession()->set(
        //     Security::LAST_USERNAME,
        //     $credentials['email']
        // );

        return $credentials;
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        // dd($credentials);
        return $this->userRepository->findOneBy(['email' => $credentials['email']]);
       
        // $token = new CsrfToken('authenticate', $credentials['csrf_token']);
        // if (!$this->csrfTokenManager->isTokenValid($token)) {
        //     throw new InvalidCsrfTokenException();
        // }

        // $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $credentials['email']]);

        // if (!$user) {
        //     // fail authentication with a custom error
        //     throw new CustomUserMessageAuthenticationException('Email could not be found.');
        // }

        // return $user;
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        // dd($user);
        // return true;
        return $this->passwordEncoder->isPasswordValid($user, $credentials['password']);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function getPassword($credentials): ?string
    {
        return $credentials['password'];
    }
    
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        // dd('success');
        if ($targetPath = $this->getTargetPath($request->getSession(), $providerKey)) {
            return new RedirectResponse($targetPath);
        }

        // For example : return new RedirectResponse($this->urlGenerator->generate('some_route'));
        // throw new \Exception('TODO: provide a valid redirect inside '.__FILE__);
        return new RedirectResponse($this->urlGenerator->generate('admin_ads_index'));
    }

    protected function getLoginUrl()
    {
        return $this->urlGenerator->generate('admin_account_login');
    }
}
