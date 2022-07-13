<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use App\Form\OfferAdminType;
use App\Controller\Admin\SectionCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class ProductCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Product::class;
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
            ->add('vendor')
            ->add('sections')
            ->add('active')
            ->add('vatRate')
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('name');
        yield TextField::new('vendor');

        if (Crud::PAGE_INDEX === $pageName || $pageName ===Crud::PAGE_DETAIL){
            yield CollectionField::new('sections');
        }
        else{
            yield AssociationField::new('sections')->setCrudController(SectionCrudController::class);
        }

        if (Crud::PAGE_EDIT === $pageName){
            yield CollectionField::new('offers')->setEntryType(OfferAdminType::class);
        }

        if (Crud::PAGE_DETAIL === $pageName){
            yield ArrayField::new('offers');
        }
        yield BooleanField::new('active');
        yield NumberField::new('vatRate');
    }
}
