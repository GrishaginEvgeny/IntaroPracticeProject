<?php

namespace App\Security;

use App\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\Container;
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
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
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

    public function __construct(ManagerRegistry $doctrine, UserRepository $userRepository, UrlGeneratorInterface $urlGenerator, $crmConfigPath='../config/apiKey.ini')
    {
        $this->apiKey = $_ENV['RETAIL_CRM_API_KEY'];
        $this->urlGenerator = $urlGenerator;
        $this->doctrine = $doctrine;
        $this->userRepository = $userRepository;
    }

    public function getCrmUser($client, $email)
    {
        $usersRequest = new UsersRequest();
        $usersRequest->filter = new ApiUserFilter();
        $usersRequest->filter->email = $email;
        try {
            $usersResponse = $client->users->list($usersRequest);
        } catch (ApiExceptionInterface | ClientExceptionInterface $exception) {
            echo $exception; // Every ApiExceptionInterface instance should implement __toString() method.
            exit(-1);
        }
        if (0 === count($usersResponse->users)) {
            echo 'User is not found.';
            return false;
        }
        return true;
    }

    public function getCrmCustomer($client, $email)
    {
        $customersRequest = new CustomersRequest();
        $customersRequest->filter = new CustomerFilter();
        $customersRequest->filter->email = $email;
        try {
            $customersResponse = $client->customers->list($customersRequest);
        } catch (ApiExceptionInterface | ClientExceptionInterface $exception) {
            echo $exception; // Every ApiExceptionInterface instance should implement __toString() method.
            exit(-1);
        }
        if (0 === count($customersResponse->customers)) {
            echo 'Customer is not found.';
            return false;
        }
        return true;
    }

    public function authenticate(Request $request): Passport
    {
        $client = SimpleClientFactory::createClient('https://popova.retailcrm.ru', $this->apiKey);
        $email = $request->request->get('email', '');
        $request->getSession()->set(Security::LAST_USERNAME, $email);
        $user = $this->userRepository->findOneByEmail($email);
        if ($user) {
            if (self::getCrmUser($client, $email)) {
                $user->setRoles(['ROLE_ADMIN']);
            } elseif (self::getCrmCustomer($client, $email)) {
                $user->setRoles(['ROLE_USER']);
            }
        }
        
        //$user->setRoles(['ROLE_USER']);
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
        $user=$this->userRepository->findOneByEmail($email);

        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return new RedirectResponse($this->urlGenerator->generate('app_admin')); //как сгенерируем админ панель поменяю роут
        }
        return new RedirectResponse($this->urlGenerator->generate('app_home'));
        // throw new \Exception('TODO: provide a valid redirect inside '.__FILE__);
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}
