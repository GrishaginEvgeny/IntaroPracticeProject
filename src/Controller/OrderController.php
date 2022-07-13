<?php

namespace App\Controller;

use App\Entity\Section;
use App\Entity\ShopCart;
use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use RetailCrm\Api\Enum\CountryCodeIso3166;
use RetailCrm\Api\Enum\Customers\CustomerType;
use RetailCrm\Api\Factory\SimpleClientFactory;
use RetailCrm\Api\Interfaces\ApiExceptionInterface;
use RetailCrm\Api\Model\Entity\Orders\Delivery\OrderDeliveryAddress;
use RetailCrm\Api\Model\Entity\Orders\Delivery\SerializedOrderDelivery;
use RetailCrm\Api\Model\Entity\Orders\Items\Offer;
use RetailCrm\Api\Model\Entity\Orders\Items\OrderProduct;
use RetailCrm\Api\Model\Entity\Orders\Items\Unit;
use RetailCrm\Api\Model\Entity\Orders\Order;
use RetailCrm\Api\Model\Entity\Orders\Payment;
use RetailCrm\Api\Model\Entity\Orders\SerializedRelationCustomer;
use RetailCrm\Api\Model\Request\Orders\OrdersCreateRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrderController extends AbstractController
{
    #[Route('/order', name: 'app_order')]
    public function index(ManagerRegistry $doctrine): Response
    {
        $mail = $this->getUser()->getUserIdentifier();

        $client = SimpleClientFactory::createClient('https://popova.retailcrm.ru', $_ENV['RETAIL_CRM_API_KEY']);

        $cartOffers = $doctrine->getRepository(ShopCart::class)->findBy(array('email' => $mail));

        $request = new OrdersCreateRequest();
        $order = new Order();
        $delivery = new SerializedOrderDelivery();
        $deliveryAddress = new OrderDeliveryAddress();
        $payment = new Payment();


        $payment->status = "Не оплачен";
        $payment->type = $_POST["paymentType"];

        $deliveryAddress->countryIso = CountryCodeIso3166::RUSSIAN_FEDERATION;
        $deliveryAddress->text = $_POST["address"];

        $delivery->address = $deliveryAddress;
        $delivery->cost = 0;
        $delivery->netCost = 0;


        foreach ($cartOffers as $cartOffer) {
            $offer = new Offer();
            $item = new OrderProduct();
            $offer->externalId = $cartOffer->getOfferId()->getId();
            $offer->unit = new Unit('796', $cartOffer->getOfferId()->getUnit(), 'pcs');
            $item->offer = $offer;
            $item->quantity = $cartOffer->getCount();
            $order->items[] = $item;
        }

        $user = $doctrine->getRepository(User::class)->findOneBy(['email' => $mail]);
        $order->delivery = $delivery;
        $order->customer = SerializedRelationCustomer::withIdAndType(
            $client->customers->get($user->getId())->customer->id,
            CustomerType::CUSTOMER
        );
        $request->order = $order;
        $request->site = "Khalif";

        $response = $client->orders->create($request);

        $em = $doctrine->getManager();
        foreach ($cartOffers as $cartOffer) {
            $em->remove($cartOffer);
        }
        $em->flush();

        return $this->redirectToRoute('app_history');
    }
}
