<?php

namespace App\Controller;

use App\Entity\Offer;
use App\Entity\Section;
use App\Services\XmlGenerator;
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
        $limit = 8;

        XmlGenerator::loadToXML($doctrine);

        $header = $doctrine
            ->getRepository(Section::class)
            ->getHeaderSections();

        $body = $doctrine
            ->getRepository(Offer::class)
            ->getOffersFromHomePage($limit);

        return $this->render('home/index.html.twig', [
            'header' => $header,
            'body' => $body
        ]);
    }
}
