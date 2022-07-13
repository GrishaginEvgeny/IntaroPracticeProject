<?php

namespace App\Controller;
use App\Entity\OrderLk;
use App\Entity\ItemLk;
use App\Entity\Section;
use Doctrine\Persistence\ManagerRegistry;
use RetailCrm\Api\Exception\Client\BuilderException;
use RetailCrm\Api\Factory\SimpleClientFactory;
use RetailCrm\Api\Interfaces\ApiExceptionInterface;
use RetailCrm\Api\Interfaces\ClientExceptionInterface;
use RetailCrm\Api\Model\Callback\Response\Payments\Item;
use RetailCrm\Api\Model\Filter\Customers\CustomerFilter;
use RetailCrm\Api\Model\Filter\Orders\OrderFilter;
use RetailCrm\Api\Model\Filter\Users\ApiUserFilter;
use RetailCrm\Api\Model\Request\Customers\CustomersRequest;
use RetailCrm\Api\Model\Request\Orders\OrdersRequest;
use RetailCrm\Api\Model\Request\Users\UsersRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LkController extends AbstractController
{
    /**
     * @throws BuilderException
     */
    #[Route('/lk', name: 'app_lk')]
    public function index(ManagerRegistry $doctrine): Response
    {
        $user=$this->getUser();
        if ($user) {

            $header = $doctrine
                ->getRepository(Section::class)
                ->getHeaderSections();
            $mail = $this->getUser()->getUserIdentifier();
            $apiKey = $_ENV['RETAIL_CRM_API_KEY'];
            $client = SimpleClientFactory::createClient('https://popova.retailcrm.ru', $apiKey);


            $customersRequest = new CustomersRequest();
            $customersRequest->filter = new CustomerFilter();
            $customersRequest->filter->email = $mail;
            try {
                $customersResponse = $client->customers->list($customersRequest);
            } catch (ApiExceptionInterface|ClientExceptionInterface $exception) {
                echo $exception;
                die();
            }
            //Авторизованный пользователь с данными из Апи Retail
            $customerLk = $customersResponse->customers;
            if (isset($customerLk[0]->{'address'}->{'text'})) {

                $address = $customerLk[0]->{'address'}->{'text'};
            } else {
                $address = 'не указан';
            }
            $sex = $customerLk[0]->{'presumableSex'};
            $sex = match ($sex) {
                'male' => "Мужской",
                'female' => "Женский",
                default => "Неизвестный",
            };
            return $this->render('lk/index.html.twig', [
                'email' => $customerLk[0]->{'email'},
                'number' => 123,
                'fName' => $customerLk[0]->{'firstName'},
                'lName' => $customerLk[0]->{'lastName'},
                'patronymic' => $customerLk[0]->{'patronymic'},
                'birthday' => $customerLk[0]->{'birthday'},
                'sex' => $sex,
                'address' => $address,
                'header' => $header,
            ]);
        }
        else return $this->redirectToRoute('app_login');
    }

}
