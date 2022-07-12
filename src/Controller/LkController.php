<?php

namespace App\Controller;

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
    public function index(): Response
    {

        $mail = 'nanana123@gmail.com';
        //$mail = $this->getUser()->getUserIdentifier();
        $apiKey = $_ENV['RETAIL_CRM_API_KEY'];

        $client = SimpleClientFactory::createClient('https://popova.retailcrm.ru', $apiKey);


        $customersRequest = new CustomersRequest();
        $customersRequest->filter = new CustomerFilter();
        $customersRequest->filter->email = $mail;
        try {
            $customersResponse = $client->customers->list($customersRequest);
        } catch (ApiExceptionInterface | ClientExceptionInterface $exception) {
            echo $exception;
            die();
        }
        //Авторизованный пользователь с данными из Апи Retail
        $customerLk = $customersResponse->customers;
        if (isset($customerLk[0]->{'address'}->{'text'})){

            $address =$customerLk[0]->{'address'}->{'text'};
        }else{
            $address ='не указан';
        }


        $ordersRequest = new OrdersRequest();
        $ordersRequest->filter = new OrderFilter();
        $ordersRequest->filter->email = $mail;
        try {
            $ordersResponse = $client->orders->list($ordersRequest);
        } catch (ApiExceptionInterface | ClientExceptionInterface $exception) {
            echo $exception;
            die();
        }

        $orders = [];
        for ($i = 0; $i < count($ordersResponse->orders); $i++) {
            if (count($ordersResponse->orders[$i]->{'payments'}) == 0){
                $typePaymentsOrder ='не произведена';
            }else{
                //$typePaymentsOrder = $ordersResponse->orders[$i]->{'payments'}[0]->{'type'};
                $typePaymentsOrder ='как-то оплатил';
            }
            $orderTarget = new OrderLk();
            $orderTarget->number = $ordersResponse->orders[$i]->{'number'};
            $orderTarget->date = $ordersResponse->orders[$i]->{'createdAt'};
            $orderTarget->status = $ordersResponse->orders[$i]->{'status'};
            $orderTarget->typePayment = $typePaymentsOrder;
            $orderTarget->summ = $ordersResponse->orders[$i]->{'summ'};
            for ($j = 0; $j < count($ordersResponse->orders[$i]->{'items'}); $j++) {
                $sizeItem ='не указано';
                $colorItem ='не указано';
                if (isset($ordersResponse->orders[$i]->{'items'}[$j]->{'offer'}->{'properties'}['size']))
                    $sizeItem =$ordersResponse->orders[$i]->{'items'}[$j]->{'offer'}->{'properties'}['size'];
                if (isset($ordersResponse->orders[$i]->{'items'}[$j]->{'offer'}->{'properties'}['color']))
                    $colorItem =$ordersResponse->orders[$i]->{'items'}[$j]->{'offer'}->{'properties'}['color'];
                $itemTarget = new ItemLk();
                $itemTarget->name = $ordersResponse->orders[$i]->{'items'}[$j]->{'offer'}->{'displayName'};
                $itemTarget->size = $sizeItem;
                $itemTarget->color =$colorItem;
                $itemTarget->summ = $ordersResponse->orders[$i]->{'items'}[$j]->{'initialPrice'};
                $itemTarget->quantity = $ordersResponse->orders[$i]->{'items'}[$j]->{'quantity'};
                array_push($orderTarget->items, $itemTarget);
            }
            array_push($orders, $orderTarget);
        }
        return $this->render('lk/index.html.twig', [
            'email' => $customerLk[0]->{'email'},
            'number' => $customerLk[0]->{'phones'}[0]->{'number'},
            'fName' => $customerLk[0]->{'firstName'},
            'lName' => $customerLk[0]->{'lastName'},
            'patronymic' => $customerLk[0]->{'patronymic'},
            'birthday' => $customerLk[0]->{'birthday'},
            'sex' => $customerLk[0]->{'presumableSex'},
            'address' => $address,
            'orders' => $orders,
        ]);
    }
}
class OrderLk {
    public $number;
    public $date;
    public $status;
    public $typePayment;
    public $summ;
    public $items = [];

    function __construct()
    {
        $this->typePayment = "не оплачено";
        $this->status = "не оплачено";
    }
}
class ItemLk {
    public $name;
    public $size;
    public $color;
    public $quantity;
    public $summ;

    function __construct()
    {
        $this->size = "-";
        $this->color = "-";
    }
}