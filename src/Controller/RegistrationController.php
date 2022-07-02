<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use RetailCrm\Api\Factory\SimpleClientFactory;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use RetailCrm\Api\Model\Entity\Customers\Customer;
use RetailCrm\Api\Model\Filter\Users\ApiUserFilter;
use RetailCrm\Api\Model\Request\Users\UsersRequest;
use Symfony\Contracts\Translation\TranslatorInterface;
use RetailCrm\Api\Model\Entity\Customers\CustomerPhone;
use RetailCrm\Api\Model\Request\Customers\CustomersCreateRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RegistrationController extends AbstractController
{
    private $apiKey;
    private SimpleClientFactory $clientFactory;
    
    public function __construct(SimpleClientFactory $clientFactory)
    {
        $this->clientFactory = $clientFactory;
    }

    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    { 
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword(
            $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager->persist($user);
            $entityManager->flush();


            if (!($crmConfig= parse_ini_file("../config/apiKey.ini"))) {
                throw new FileNotFoundException("../config/apiKey.ini");
            }
            $this->apiKey =$crmConfig['apiKey'];

            $client = SimpleClientFactory::createClient('https://popova.retailcrm.ru', $this->apiKey);
            $request = new CustomersCreateRequest();
            $request->customer = new Customer();
            
            $request->site = 'practice-2022';
            $request->customer->externalId = (string)$user->getId();
            $request->customer->email = $form->get('email')->getData();
            $request->customer->firstName = $form->get('firstname')->getData();
            $request->customer->lastName = $form->get('lastname')->getData();
            $request->customer->patronymic = $form->get('patronymic')->getData();
            $request->customer->phones = [new CustomerPhone()];
            $request->customer->phones[0]->number = $form->get('phone')->getData();
            $request->customer->birthday = $form->get('birthdate')->getData();
            $request->customer->sex = $form->get('sex')->getData();


            try {
                $response = $client->customers->create($request);
            } catch (ApiExceptionInterface | ClientExceptionInterface $exception) {
                echo $exception; // Every ApiExceptionInterface instance should implement __toString() method.
                $entityManager->remove($user);
                exit(-1);
            }  

            return $this->redirectToRoute('_profiler_home');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
