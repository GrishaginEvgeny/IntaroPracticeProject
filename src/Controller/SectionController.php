<?php

namespace App\Controller;

use App\Entity\Offer;
use App\Entity\Section;
use Doctrine\DBAL\Exception;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SectionController extends AbstractController
{
    /**
     * @throws Exception
     */
    #[Route('/section/{id}', name: 'app_section')]
    public function index(int $id, ManagerRegistry $doctrine): Response
    {
        $section = $doctrine->getRepository(Section::class)->findOneBy(['id' => $id]);

        if (!$section) {
            return $this->redirectToRoute('app_home');
        }

        $parentSections = $doctrine
            ->getRepository(Section::class)
            ->getParentSections($section->getId());

        $header = $doctrine
            ->getRepository(Section::class)
            ->getHeaderSections();

        $products = $doctrine
            ->getRepository(Section::class)
            ->getSectionOffers($section->getId());

        return $this->render('section/index.html.twig', [
            'header' => $header,
            'parentSections' => $parentSections,
            'products' => $products,
        ]);
    }
}
