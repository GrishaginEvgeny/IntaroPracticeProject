<?php

namespace App\Controller;

use App\Entity\Offer;
use App\Entity\Section;
use Doctrine\DBAL\Exception;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;

class SectionController extends AbstractController
{
    /**
     * @throws Exception
     */
    #[Route('/section/{id}', name: 'app_section')]
    public function index(int $id, ManagerRegistry $doctrine, Request $request, PaginatorInterface $paginator): Response
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

        $productsBeforePaginate = $doctrine
            ->getRepository(Section::class)
            ->getSectionOffers($section->getId());

        $productsOnPage = 6;

        /**
         * текущая страница
         */
        $currentPage = $request->query->getInt('page', 1);

        /**
         * флаг предыдущей страницы
         */
        $hasPreviousPage = false;
        if ($currentPage != 1) {
            $hasPreviousPage = true;
        }

        /**
         * флаг следующей страницы
         */
        $hasNextPage = false;
        if ((count($productsBeforePaginate) - $productsOnPage * $currentPage) > 0) {
            $hasNextPage = true;
        }

        /**
         * пагинация записей
         */
        $products = $paginator->paginate(
            $productsBeforePaginate,
            $currentPage,
            $productsOnPage
        );

        return $this->render('section/index.html.twig', [
            'header' => $header,
            'parentSections' => $parentSections,
            'products' => $products,
            'hasPreviousPage' => $hasPreviousPage,
            'currentPage' => $currentPage,
            'hasNextPage' => $hasNextPage,
        ]);
    }
}
