<?php

namespace App\Controller;

use App\Entity\Offer;
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

        $sections = $doctrine
            ->getRepository(Section::class)
            ->getSectionsByProduct($product->getId());

        $resultSet = $doctrine
            ->getRepository(Offer::class)
            ->getOfferInfo($id);
        $offerInfo = [];
        for ($i = 0; $i < count($resultSet);) {
            $settings = [];
            for ($j = $i; $j < count($resultSet); $j++) {
                if ($resultSet[$i]['o_id'] == $resultSet[$j]['o_id']) {
                    $settings[] = [
                        'name' => $resultSet[$j]['name'],
                        'code' => $resultSet[$j]['code'],
                        'sort' => $resultSet[$j]['sort'],
                        'value' => $resultSet[$j]['value'],
                    ];
                }
            }
            $offerInfo[$i] = [
                'o_id' => $resultSet[$i]['o_id'],
                'price' => $resultSet[$i]['price'],
                'picture' => $resultSet[$i]['picture'],
                'settings' => $settings,
            ];
            $i += count($settings);
        }

        return $this->render('product/index.html.twig', [
            'header' => $header,
            'product' => $product,
            'sections' => $sections,
            'offerInfo' => $offerInfo,
        ]);
    }
}
