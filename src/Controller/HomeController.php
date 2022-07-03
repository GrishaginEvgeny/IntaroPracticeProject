<?php

namespace App\Controller;

use App\Entity\Offer;
use App\Entity\Section;
use Doctrine\DBAL\Exception;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @throws Exception
     */
    #[Route('/', name: 'app_home')]
    public function index(ManagerRegistry $doctrine): Response
    {
        $header = $doctrine
            ->getRepository(Section::class)
            ->getHeaderSections();

        $body = [];

        for ($i = 0; $i < count($header); $i++) {
            $products = $doctrine
                ->getRepository(Section::class)
                ->getSectionOffers($header[$i]['id']);

            $body[] = [
                'id' => $header[$i]['id'],
                'name' => $header[$i]['name'],
                'products' => $products
            ];
        }

        return $this->render('home/index.html.twig', [
            'header' => $header,
            'body' => $body
        ]);
    }
}
