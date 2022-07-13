<?php

namespace App\Controller\Admin;

use App\Form\PropertyType;
use App\Entity\PropertyValue;
use App\Controller\Admin\OfferCrudController;
use App\Controller\Admin\PropertyCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class PropertyValueCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return PropertyValue::class;
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
            ->add('offer')
            ->add('property')
            ->add('value')
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        
        if (Crud::PAGE_INDEX === $pageName || $pageName ===Crud::PAGE_DETAIL){
            yield TextField::new('property');
            yield AssociationField::new('offer');
        }
        else{
            yield AssociationField::new('property')->setCrudController(PropertyCrudController::class);
            yield AssociationField::new('offer')->setCrudController(OfferCrudController::class);
        }
        yield TextField::new('value');
    }
}
