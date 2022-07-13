<?php

namespace App\Controller\Admin;

use App\Entity\Offer;
use App\Form\OfferAdminType;
use App\Form\PropertyValueType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

#[Route('/offers', name: 'admin_offers')]
class OfferCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Offer::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            // ...
            ->add(Crud::PAGE_INDEX, Action::DETAIL);
            // ->add(Crud::PAGE_EDIT, Action::SAVE_AND_ADD_ANOTHER)
        ;
    }
    
    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('name')
            ->add('price')
            ->add('quantity')
            ->add('unit')
            ->add('product')
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('name');
        yield MoneyField::new('price')->setCurrency("RUB");
        yield IntegerField::new('quantity');
        yield TextField::new('unit');
        if (Crud::PAGE_EDIT === $pageName || Crud::PAGE_DETAIL === $pageName) {
            yield ImageField::new('picture')
        // ->setBasePath('uploads/pictures')
        ->setUploadDir('\public\upload\pictures');
        }
        if (Crud::PAGE_EDIT === $pageName){
            yield AssociationField::new('product');
        }
        if (Crud::PAGE_INDEX === $pageName || $pageName ===Crud::PAGE_DETAIL){
            yield CollectionField::new('propertyValues');
        }
        else{
            yield AssociationField::new('propertyValues')->setCrudController(PropertyValueCrudController::class);
        }
    }
}
