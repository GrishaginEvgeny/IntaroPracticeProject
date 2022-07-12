<?php

namespace App\Controller\Admin;

use App\Entity\Offer;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class OfferCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Offer::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('name'),
            MoneyField::new('price')->setCurrency("RUB"),
            IntegerField::new('quantity'),
            TextField::new('unit'),
            ImageField::new('picture')
                ->setBasePath('upload/pictures/')
                ->setUploadDir('public/upload/pictures/')
        ];
    }
}
