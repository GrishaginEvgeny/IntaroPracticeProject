<?php

namespace App\Controller;

use App\Entity\Section;
use DateInterval;
use DateTime;
use Doctrine\Persistence\ManagerRegistry;
use RetailCrm\Api\Interfaces\ClientExceptionInterface;
use RetailCrm\Api\Enum\CountryCodeIso3166;
use RetailCrm\Api\Enum\Customers\CustomerType;
use RetailCrm\Api\Factory\SimpleClientFactory;
use RetailCrm\Api\Interfaces\ApiExceptionInterface;
use RetailCrm\Api\Model\Entity\Orders\Delivery\OrderDeliveryAddress;
use RetailCrm\Api\Model\Entity\Orders\Delivery\SerializedOrderDelivery;
use RetailCrm\Api\Model\Entity\Orders\Items\Offer;
use RetailCrm\Api\Model\Entity\Orders\Items\OrderProduct;
use RetailCrm\Api\Model\Entity\Orders\Items\PriceType;
use RetailCrm\Api\Model\Entity\Orders\Items\Unit;
use RetailCrm\Api\Model\Entity\Orders\Order;
use RetailCrm\Api\Model\Entity\Orders\Payment;
use RetailCrm\Api\Model\Entity\Orders\SerializedRelationCustomer;
use RetailCrm\Api\Model\Entity\Store\ProductProperty;
use RetailCrm\Api\Model\Request\Orders\OrdersCreateRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BotApiController extends AbstractController
{
    #[Route('/api/order/create', name: 'api_order_post')]
    function createOrder(ManagerRegistry $doctrine) : Response
    {
        $strErrorDesc = '';
        $requestMethod = $_SERVER["REQUEST_METHOD"];

        if (strtoupper($requestMethod) != 'POST') {
            $data['message'] = 'Wrong method! Should use POST method.';
            $data['statusCode'] = 400;
            return $this->render('botApi/botApi.html.twig', [
                'json' => json_encode($data),
            ]);
        }

        if (!isset($_POST['email']) || !isset($_POST['phone']) || !isset($_POST['external_id'])) {
            $data['message'] = 'email, phone and external_id parameters should be filled.';
            $data['statusCode'] = 400;
            return $this->render('botApi/botApi.html.twig', [
                'json' => json_encode($data),
            ]);
        }

        $client = SimpleClientFactory::createClient('https://popova.retailcrm.ru', 'eVsrX4drzsw35chftqiSbTbGgbLtaPbN');

        $productOffer = $doctrine->getRepository(\App\Entity\Offer::class)->findOneBy(array('id' => $_POST['external_id']));

        if ($productOffer == null) {
            $data['message'] = 'Offer in not found.';
            $data['statusCode'] = 400;
            return $this->render('botApi/botApi.html.twig', [
                'json' => json_encode($data),
            ]);
        }

        $request = new OrdersCreateRequest();
        $order = new Order();
        $delivery = new SerializedOrderDelivery();
        $deliveryAddress = new OrderDeliveryAddress();
        $offer = new Offer();
        $item = new OrderProduct();

        $deliveryAddress->countryIso = CountryCodeIso3166::RUSSIAN_FEDERATION;

        $delivery->address = $deliveryAddress;
        $delivery->cost = 0;
        $delivery->netCost = 0;

        $offer->externalId = $_POST['external_id'];
        $offer->unit = new Unit('796', 'шт', 'pcs');

        $item->offer = $offer;
        $item->quantity = 1;

        $order->delivery = $delivery;
        $order->items = [$item];
        $order->orderMethod = 'phone';
        $order->email = $_POST['email'];
        $order->countryIso = CountryCodeIso3166::RUSSIAN_FEDERATION;
        $order->phone = $_POST['phone'];
        $order->weight = 1000;

        $request->order = $order;



        try {
            $response = $client->orders->create($request);
        }
        catch (ApiExceptionInterface $exception) {
                $data['message'] = 'Something went wrong.';
                $data['statusCode'] = 400;
                return $this->render('botApi/botApi.html.twig', [
                'json' => json_encode($data),
            ]);
        }
        $data['message'] = "";
        $data['statusCode'] = 200;
        return $this->render('botApi/botApi.html.twig', [
            'json' => json_encode($data),
        ]);
    }

    #[Route('/api/product/{id}', name: 'api_product_get')]
    function getProductValues(int $id, ManagerRegistry $doctrine) : Response
    {
        $strErrorDesc = '';
        $requestMethod = $_SERVER["REQUEST_METHOD"];

        if (strtoupper($requestMethod) != 'GET') {
            $data['message'] = 'Wrong method! Should use GET method.';
            $data['statusCode'] = 400;
            return $this->render('botApi/botApi.html.twig', [
                'json' => json_encode($data),
            ]);
        }

        if ($id == null) {
            $data['message'] = 'id parameter should be filled.';
            $data['statusCode'] = 400;
            return $this->render('botApi/botApi.html.twig', [
                'json' => json_encode($data),
            ]);
        }

        $product = $doctrine->getRepository(\App\Entity\Product::class)->findOneBy(['id' => $id]);

        $productOffers = $product->getOffers();

        $j = 0;
        foreach ($productOffers as $offer) {
            $data['offers'][$j]['id'] = $offer->getId();
            $data['offers'][$j]['price'] = $offer->getPrice();
            $propertyValues = $offer->getPropertyValues();
            $i = 0;
            foreach ($propertyValues as $propertyValue) {
              $data['offers'][$j]['properties'][$i]['name'] = $propertyValue->getProperty()->getName();
              $data['offers'][$j]['properties'][$i]['value'] = $propertyValue->getValue();
              $i++;
            }
            $j++;
        }

        return $this->render('botApi/botApi.html.twig', [
            'json' => json_encode($data),
        ]);
    }


    #[Route('/api/productToOffer/{id}/{color}/{size}', name: 'api_product_to_offer')]
    function productToOffer(int $id, string $color, string $size, ManagerRegistry $doctrine): Response
    {
        $requestMethod = $_SERVER["REQUEST_METHOD"];

        if (strtoupper($requestMethod) != 'GET') {
            $data['message'] = 'Wrong method! Should use GET method.';
            $data['statusCode'] = 400;
            return $this->render('botApi/botApi.html.twig', [
                'json' => json_encode($data),
            ]);
        }

        if ($id == null || $color == null || $size == null) {
            $data['message'] = 'all parameters should be filled';
            $data['statusCode'] = 400;
            return $this->render('botApi/botApi.html.twig', [
                'json' => json_encode($data),
            ]);
        }

        $product = $doctrine->getRepository(\App\Entity\Product::class)->findOneBy(['id' => $id]);
        $productOffers = $product->getOffers();

        $j = 0;
        foreach ($productOffers as $offer) {
            $propertyValues = $offer->getPropertyValues();
            $i = 0;
            foreach ($propertyValues as $propertyValue) {

                $data['offers'][$j]['id'] = $offer->getId();
                if($propertyValue->getValue()==$color) $data['offers'][$j]['color'] = $propertyValue->getValue();
                if($propertyValue->getValue()==$size) $data['offers'][$j]['size'] = $propertyValue->getValue();
                $i++;
                if(count($data['offers'][$j])==3){
                    return $this->render('botApi/botApi.html.twig', [
                        'json' => json_encode($data['offers'][$j]),
                    ]);
                }
            }
            $j++;
        }
        return $this->render('botApi/botApi.html.twig', [
            'json' => json_encode($data['offers']),
        ]);
    }
}