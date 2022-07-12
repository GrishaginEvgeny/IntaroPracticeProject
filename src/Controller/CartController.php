<?php

namespace App\Controller;

use App\Entity\Offer;
use App\Entity\Section;
use App\Entity\ShopCart;
use App\Repository\ShopCartRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
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
        $header = $doctrine
            ->getRepository(Section::class)
            ->getHeaderSections();
        $user=$this->getUser();
        if ($user) {
            //выводим все товары юзера
            $offers = $cartRepository->findBy(
                ['email' => $user->getUserIdentifier()],
                ['id' => 'ASC']
            );
            $totalPrice=0;
            //сразу считаем итоговую стоимость
            if($offers) {
                foreach ($offers as $offer) {
                    $totalPrice += $offer->getOfferId()->getPrice() * $offer->getCount();
                }
            }
            return $this->render('cart/index.html.twig', [
                'offers' => $offers,
                'totalPrice' => $totalPrice,
                'header' => $header,
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
