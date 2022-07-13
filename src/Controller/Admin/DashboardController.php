<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Entity\Offer;
use App\Entity\Product;
use App\Entity\Section;
use App\Entity\Property;
use App\Entity\PropertyValue;
use Symfony\Component\HttpFoundation\Response;
use App\Controller\Admin\ProductCrudController;
use Symfony\Component\Routing\Annotation\Route;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;

/**
 * @IsGranted("ROLE_ADMIN")
 */
class DashboardController extends AbstractDashboardController
{
    #[Route('/admin', name: 'app_admin')]
    public function index(): Response
    {
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        $url=$adminUrlGenerator->setController(ProductCrudController::class)->generateUrl();
        return $this->redirect($url);
        // return parent::index();

        // Option 1. You can make your dashboard redirect to some common page of your backend
        //
        // $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        // return $this->redirect($adminUrlGenerator->setController(OneOfYourCrudController::class)->generateUrl());

        // Option 2. You can make your dashboard redirect to different pages depending on the user
        //
        // if ('jane' === $this->getUser()->getUsername()) {
        //     return $this->redirect('...');
        // }

        // Option 3. You can render some custom template to display a proper dashboard with widgets, etc.
        // (tip: it's easier if your template extends from @EasyAdmin/page/content.html.twig)
        //
        // return $this->render('some/path/my-dashboard.html.twig');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Админ панель')
            ->disableUrlSignatures();
    }

    public function configureMenuItems(): iterable
    {
        return [
            //MenuItem::linkToDashboard('Dashboard', 'fa fa-home'),
            MenuItem::linkToRoute('На главную', 'fas fa-home', 'app_home'),
            MenuItem::section('Разделы'),
            MenuItem::linkToCrud('Продукты | Products', 'fas fa-list', Product::class),
            MenuItem::linkToCrud('Предложения | Offers', 'fas fa-list', Offer::class),
            MenuItem::linkToCrud('Секции | Sections', 'fas fa-list', Section::class),
            MenuItem::linkToCrud('Пользователи | Users', 'fas fa-list', User::class),
            MenuItem::linkToCrud('Свойства продукта | Product properties', 'fas fa-list', PropertyValue::class),
            MenuItem::linkToCrud('Свойства | Properties', 'fas fa-list', Property::class),
        ];
    }
}
