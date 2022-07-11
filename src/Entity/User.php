<?php

namespace App\Entity;

use Doctrine\ORM\Mapping\Id;
use Symfony\Component\Uid\Uuid;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Column;
use App\Repository\UserRepository;
use RetailCrm\Api\Factory\SimpleClientFactory;
use RetailCrm\Api\Interfaces\ApiExceptionInterface;
use RetailCrm\Api\Model\Filter\Users\ApiUserFilter;
use RetailCrm\Api\Model\Request\Users\UsersRequest;
use RetailCrm\Api\Interfaces\ClientExceptionInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use RetailCrm\Api\Model\Filter\Customers\CustomerFilter;
use RetailCrm\Api\Model\Request\Customers\CustomersRequest;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @ORM\Table(name="`user`")
 */
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * @Id
     * @Column(type="uuid")
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator("doctrine.uuid_generator")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $email;

    private $roles = [];

    /**
     * @ORM\Column(type="string", length=180)
     */
    private $password;


    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    

    public function getCrmUser($client, $email)
    {
        $usersRequest = new UsersRequest();
        $usersRequest->filter = new ApiUserFilter();
        $usersRequest->filter->email = $email;
        try {
            $usersResponse = $client->users->list($usersRequest);
            if (0 === count($usersResponse->users)) return false;
            else return true;
        } catch (ApiExceptionInterface | ClientExceptionInterface $exception) {
            echo $exception;
            exit(-1);
        }
    }

    public function getCrmCustomer($client, $email)
    {
        $customersRequest = new CustomersRequest();
        $customersRequest->filter = new CustomerFilter();
        $customersRequest->filter->email = $email;
        try {
            $customersResponse = $client->customers->list($customersRequest);
            if (0 === count($customersResponse->customers)) return false;
            else return true;
        } catch (ApiExceptionInterface | ClientExceptionInterface $exception) {
            echo $exception;
            exit(-1);
        }
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';
        $client = SimpleClientFactory::createClient('https://popova.retailcrm.ru', $_ENV['RETAIL_CRM_API_KEY']);
        if (self::getCrmUser($client, $this->email)) {
            $roles[] = 'ROLE_ADMIN';
        }
        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }
}
