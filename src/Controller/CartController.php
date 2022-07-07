<?php

namespace App\Controller;

use App\Entity\Offer;
use App\Entity\ShopCart;
use App\Repository\ShopCartRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class CartController extends AbstractController
{
    private SessionInterface $session;

    /**
     * возможно сюда можно передавать сущность User, а не его id, с другой стороны зачем
     * у меня была какая-то проблема с логином поэтому я пока так оставил
     *
     * @param ShopCartRepository $cartRepository
     * @return Response
     */
    #[Route('/cart', name: 'cart')]
    public function index(ShopCartRepository $cartRepository): Response
    {

        $userId = '1';
        $products = $cartRepository->findBy(
            ['user_id' => $userId],
            ['id' => 'ASC']
        );
        $totalPrice=0;
        foreach ($products as $product){
            $totalPrice+=$product->getOfferId()->getPrice()*$product->getCount();
        }
        //shopCart хранит в себе сущности офферов
        return $this->render('cart/index.html.twig', [
            'products' => $products,
            'totalPrice' => $totalPrice
        ]);
    }

    /**
     * возможно сюда можно передавать сущность User
     * нужно будет поменять ссылку product на offer, но для начала нужно как-то сформировать offer
     * (offer сформирован, надо обновить)
     *
     * @param Offer $offers
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route('/product/{id}/to_cart', name: 'add_to_cart')]
    public function addToCart(Offer $offers, ShopCartRepository $cartRepository, EntityManagerInterface $entityManager): Response
    {
        $userId = '1';
        $product = $cartRepository->findOneBy([
            'user_id' => $userId,
            'offer_id' => $offers->getId(),
        ]);
        if(!$product){
            $shopCart = (new ShopCart())
                -> setOfferId($offers)
                -> setCount(1)
                -> setUserId($userId);
            $entityManager->persist($shopCart);
        }
        else {
            $product->setCount(($product->getCount()+1));
            $entityManager->persist($product);
        }
        $entityManager->flush();
        return $this->redirectToRoute('app_product', ['id'=>$offers->getId()]);
    }

    /** убавление товара из корзины
     *
     * @param Offer $offers
     * @param ShopCartRepository $cartRepository
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route('/cart/minus/{id}', name: 'delete_from_cart')]
    public function deleteFromCart(Offer $offers, ShopCartRepository $cartRepository, EntityManagerInterface $entityManager): Response
    {
        $userId = '1';
        $product = $cartRepository->findOneBy([
            'user_id' => $userId,
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
        $userId = '1';
        $product = $cartRepository->findOneBy([
            'user_id' => $userId,
            'offer_id' => $offers->getId(),
        ]);
        $product->setCount(($product->getCount()+1));
        $entityManager->persist($product);
        $entityManager->flush();
        return $this->redirectToRoute('cart');
    }
}
