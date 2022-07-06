<?php

namespace App\Controller;

use App\Entity\Offer;
use App\Entity\ShopCart;
use App\Repository\OfferRepository;
use App\Repository\ShopCartRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Config\Doctrine\Orm\EntityManagerConfig;

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
        $products = $cartRepository->findBy(['user_id' => $userId]);
        //shopCart хранит в себе сущности офферов
        return $this->render('cart/index.html.twig', [
            'products' => $products,
        ]);
    }

    /**
     * возможно сюда можно передавать сущность User
     * тут идет добавление по айди продукта, я делал добавление по офферу (это логичнее)
     * нужно будет поменять ссылку product на offer, но для начала нужно как-то сформировать offer
     * @param Offer $offers
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route('/product/{id}/to_cart', name: 'add_to_cart')]
    public function addToCart(Offer $offers, EntityManagerInterface $entityManager): Response
    {
        $userId = '1';
        $shopCart = (new ShopCart())
            -> setOfferId($offers)
            -> setCount(1)
            -> setUserId($userId);

        $entityManager->persist($shopCart);
        $entityManager->flush();

        return $this->redirectToRoute('app_product', ['id'=>$offers->getId()]);
    }
}
