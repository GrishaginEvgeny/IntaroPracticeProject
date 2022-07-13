<?php

namespace App\Controller;

use App\Entity\Offer;
use App\Entity\Section;
use App\Entity\ShopCart;
use App\Repository\ShopCartRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use RetailCrm\Api\Factory\SimpleClientFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class CartController extends AbstractController
{
    /**
     * страничка корзины только для авторизованных пользователей
     * если неавторизован - редирект на логин
     * @param ShopCartRepository $cartRepository
     * @return Response
     */
    #[Route('/cart', name: 'cart')]
    public function index(ShopCartRepository $cartRepository, ManagerRegistry $doctrine): Response
    {
        $user=$this->getUser();
        if ($user) {
            $header = $doctrine
                ->getRepository(Section::class)
                ->getHeaderSections();
            //выводим все товары юзера
            $offers = $cartRepository->findBy(
                ['email' => $user->getUserIdentifier()],
                ['id' => 'ASC']
            );
            $totalPrice=0;
            $arrayedOffers=[];
            //сразу считаем итоговую стоимость
            foreach ($offers as $offer){
                $offerProperty = $offer->getOfferId()->getPropertyValues();
                $properties = [];
                foreach ($offerProperty as $property){
                    $properties[$property->getProperty()->getCode()] = $property->getValue();
                }
                unset($property);
                $arrayedOffers[] = [
                    'count' => $offer->getCount(),
                    'offer_id' => $offer->getOfferId()->getId(),
                    'price' => $offer-> getOfferId()->getPrice(),
                    'picture' => $offer->getOfferId()->getPicture(),
                    'name' => $offer->getOfferId()->getName(),
                    'product_id' => $offer->getOfferId()->getProduct()->getId(),
                    'properties' => $properties,
                ];
                $totalPrice+=$offer->getOfferId()->getPrice()*$offer->getCount();
            }
            $apiKey = $_ENV['RETAIL_CRM_API_KEY'];

            $client = SimpleClientFactory::createClient('https://popova.retailcrm.ru', $apiKey);
            $deliveryTypesCrm = $client->references->deliveryTypes()->deliveryTypes;
            $typeKey =  array_keys($deliveryTypesCrm);
            $deliveryTypesLk = [];
            foreach ( $typeKey as $type) {
                $deliveryTypesLk[] = $deliveryTypesCrm[$type]->{'name'};
            }

            $paymentTypesCrm = $client->references->paymentTypes()->paymentTypes;
            $typeKey =  array_keys($paymentTypesCrm);
            $paymentTypesLk = [];
            foreach ( $typeKey as $type) {
                $paymentTypesLk[] = $paymentTypesCrm[$type]->{'name'};
            }
            //dd($arrayedOffers);

            return $this->render('cart/index.html.twig', [
                'offers' => $arrayedOffers,
                'totalPrice' => $totalPrice,
                'header' => $header,
                'deliveryTypes' => $deliveryTypesLk,
                'paymentTypes' => $paymentTypesLk,
            ]);
        }
        else return $this->redirectToRoute('app_login');
    }

    /** добавить в корзину
     * если пользователь не авторизован - редирект на логин
     * если такой-же офер не существует, записываем его в корзину, кол-во=1
     * если офер есть, увеличиваем его кол-во в корзине
     * @param Offer $offer
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route('/product/{id}/to_cart', name: 'add_to_cart')]
    public function addToCart(Offer $offer, ShopCartRepository $cartRepository, EntityManagerInterface $entityManager): Response
    {
        $user=$this->getUser();
        if($user){
            $product = $cartRepository->findOneBy([
                'email' => $user->getUserIdentifier(),
                'offer_id' => $offer->getId(),
            ]);
            if(!$product){
                $shopCart = (new ShopCart())
                    -> setOfferId($offer)
                    -> setCount(1)
                    -> setEmail($user->getUserIdentifier());
                $entityManager->persist($shopCart);
            }
            else {
                $product->setCount(($product->getCount()+1));
                $entityManager->persist($product);
            }
            $entityManager->flush();
            return $this->redirectToRoute('app_product', ['id'=>$offer->getProduct()->getId()]);
        }
        else return $this->redirectToRoute('app_login');
    }

    /** убавление из корзины
     *
     * @param Offer $offers
     * @param ShopCartRepository $cartRepository
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route('/cart/minus/{id}', name: 'delete_from_cart')]
    public function deleteFromCart(Offer $offers, ShopCartRepository $cartRepository, EntityManagerInterface $entityManager): Response
    {
        $user=$this->getUser();
        $product = $cartRepository->findOneBy([
            'email' => $user->getUserIdentifier(),
            'offer_id' => $offers->getId(),
        ]);
        $count=$product->getCount()-1;
        if (($count)<1){
            $entityManager->remove($product);
        }
        else{
            $product->setCount($count);
            $entityManager->persist($product);
        }
        $entityManager->flush();
        return $this->redirectToRoute('cart');
    }

    /** добавление (кол-ва) товара в корзине
     * @param Offer $offers
     * @param ShopCartRepository $cartRepository
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route('/cart/plus/{id}', name: 'add_count_cart')]
    public function addCountCart(Offer $offers, ShopCartRepository $cartRepository, EntityManagerInterface $entityManager): Response
    {
        $user=$this->getUser();
        $product = $cartRepository->findOneBy([
            'email' => $user->getUserIdentifier(),
            'offer_id' => $offers->getId(),
        ]);
        $product->setCount(($product->getCount()+1));
        $entityManager->persist($product);
        $entityManager->flush();
        return $this->redirectToRoute('cart');
    }
}
