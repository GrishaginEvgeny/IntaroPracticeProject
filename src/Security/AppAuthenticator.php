<?php

namespace App\Security;

use App\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry;
use RetailCrm\Api\Exception\Client\BuilderException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use RetailCrm\Api\Factory\SimpleClientFactory;
use Symfony\Component\HttpFoundation\Response;
use RetailCrm\Api\Interfaces\ApiExceptionInterface;
use RetailCrm\Api\Model\Filter\Users\ApiUserFilter;
use RetailCrm\Api\Model\Request\Users\UsersRequest;
use RetailCrm\Api\Interfaces\ClientExceptionInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use RetailCrm\Api\Model\Filter\Customers\CustomerFilter;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use RetailCrm\Api\Model\Request\Customers\CustomersRequest;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;

class AppAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';

    private $apiKey;


    private ManagerRegistry $doctrine;
    private UserRepository $userRepository;

    private UrlGeneratorInterface $urlGenerator;

    public function __construct(ManagerRegistry $doctrine, UserRepository $userRepository, UrlGeneratorInterface $urlGenerator)
    {
        $this->apiKey = $_ENV['RETAIL_CRM_API_KEY'];
        $this->urlGenerator = $urlGenerator;
        $this->doctrine = $doctrine;
        $this->userRepository = $userRepository;
    }

    /**
     * @throws BuilderException
     */
    public function authenticate(Request $request): Passport
    {
        $email = $request->request->get('email', '');
        $request->getSession()->set(Security::LAST_USERNAME, $email);
        return new Passport(
            new UserBadge($email),
            new PasswordCredentials($request->request->get('password', '')),
            [
                new CsrfTokenBadge('authenticate', $request->request->get('_csrf_token')),
            ]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }
        $email = $request->request->get('email', '');
        $user=$this->userRepository->findOneBy(['email' => $email]);

        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            // dd($user);
            return new RedirectResponse($this->urlGenerator->generate('admin'));
        }
        return new RedirectResponse($this->urlGenerator->generate('app_home'));
        // throw new \Exception('TODO: provide a valid redirect inside '.__FILE__);
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}
