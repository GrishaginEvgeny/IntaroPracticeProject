<?php

namespace App\Controller;

use App\Entity\Section;
use Doctrine\DBAL\Exception;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TestController extends AbstractController
{
    #[Route('/test', name: 'app_test')]
    public function index(ManagerRegistry $doctrine): Response
    {
        $firstLevelSections = $doctrine
            ->getRepository(Section::class)
            ->findBy(['parent' => null]);

        return $this->render('test/index.html.twig', [
            'firstLevelCatalogSections' => $firstLevelSections,
        ]);
    }
}
