<?php

namespace App\Controller;

use App\Entity\Offer;
use App\Entity\OrderLk;
use App\Entity\ItemLk;
use App\Entity\Section;
use Doctrine\Persistence\ManagerRegistry;
use RetailCrm\Api\Factory\SimpleClientFactory;
use RetailCrm\Api\Interfaces\ApiExceptionInterface;
use RetailCrm\Api\Interfaces\ClientExceptionInterface;
use RetailCrm\Api\Model\Filter\Orders\OrderFilter;
use RetailCrm\Api\Model\Request\Orders\OrdersRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HistoryController extends AbstractController
{
    #[Route('/lk/history', name: 'app_history')]
    public function index(ManagerRegistry $doctrine): Response
    {
        $user=$this->getUser();
        if ($user) {
            $header = $doctrine
                ->getRepository(Section::class)
                ->getHeaderSections();
            //$mail = $this->getUser()->getUserIdentifier();
            $mail = 'des1337481@gmail.com';
            $apiKey = $_ENV['RETAIL_CRM_API_KEY'];

            $client = SimpleClientFactory::createClient('https://popova.retailcrm.ru', $apiKey);

            $ordersRequest = new OrdersRequest();
            $ordersRequest->filter = new OrderFilter();
            $ordersRequest->filter->email = $mail;
            try {
                $ordersResponse = $client->orders->list($ordersRequest);
            } catch (ApiExceptionInterface|ClientExceptionInterface $exception) {
                echo $exception;
                die();
            }

            $orders = [];
            $cartIsEmpty = false;
            if (count($ordersResponse->orders) == 0) {
                $cartIsEmpty = true;
            }
            for ($i = 0; $i < count($ordersResponse->orders); $i++) {
                if (count($ordersResponse->orders[$i]->{'payments'}) == 0) {
                    $typePaymentsOrder = 'не произведена';
                } else {
                    $typePaymentsOrder = $ordersResponse->orders[$i]->{'payments'}[array_keys($ordersResponse->orders[$i]->{'payments'})[0]]->{'type'};
                }
                $orderTarget = new OrderLk();
                $orderTarget->number = $ordersResponse->orders[$i]->{'number'};
                $orderTarget->date = $ordersResponse->orders[$i]->{'createdAt'};
                $orderTarget->status = $ordersResponse->orders[$i]->{'status'};
                $orderTarget->typePayment = $typePaymentsOrder;
                $orderTarget->summ = $ordersResponse->orders[$i]->{'summ'};
                for ($j = 0; $j < count($ordersResponse->orders[$i]->{'items'}); $j++) {
                    $sizeItem = 'не указано';
                    $colorItem = 'не указано';
                    if (isset($ordersResponse->orders[$i]->{'items'}[$j]->{'offer'}->{'properties'}['size']))
                        $sizeItem = $ordersResponse->orders[$i]->{'items'}[$j]->{'offer'}->{'properties'}['size'];
                    if (isset($ordersResponse->orders[$i]->{'items'}[$j]->{'offer'}->{'properties'}['color']))
                        $colorItem = $ordersResponse->orders[$i]->{'items'}[$j]->{'offer'}->{'properties'}['color'];
                    $itemTarget = new ItemLk();
                    $itemTarget->name = $ordersResponse->orders[$i]->{'items'}[$j]->{'offer'}->{'displayName'};
                    $itemTarget->picture = $doctrine->getRepository(Offer::class)->getPicture($itemTarget->name);
                    $itemTarget->size = $sizeItem;
                    $itemTarget->color = $colorItem;
                    $itemTarget->summ = $ordersResponse->orders[$i]->{'items'}[$j]->{'initialPrice'};
                    $itemTarget->quantity = $ordersResponse->orders[$i]->{'items'}[$j]->{'quantity'};
                    array_push($orderTarget->items, $itemTarget);
                }
                array_push($orders, $orderTarget);
            }
            return $this->render('lk/history.html.twig', [
                'orders' => $orders,
                'header' => $header,
                'cartIsEmpty' => $cartIsEmpty,
            ]);
        }
        else return $this->redirectToRoute('app_login');
    }
}
