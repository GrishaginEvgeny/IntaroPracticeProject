<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\Section;
use Doctrine\DBAL\Exception;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractController
{
    /**
     * @throws Exception
     */
    #[Route('/product/{id}', name: 'app_product')]
    public function index(int $id, ManagerRegistry $doctrine): Response
    {
        $product = $doctrine->getRepository(Product::class)->findOneBy(['id' => $id]);

        if (!$product) {
            return $this->redirectToRoute('app_home');
        }

        $header = $doctrine
            ->getRepository(Section::class)
            ->getHeaderSections();

        return $this->render('product/index.html.twig', [
            'header' => $header,
        ]);
    }
}
