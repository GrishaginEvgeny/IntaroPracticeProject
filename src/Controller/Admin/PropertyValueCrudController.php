<?php

namespace App\Controller\Admin;

use App\Form\PropertyType;
use App\Entity\PropertyValue;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class PropertyValueCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return PropertyValue::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            CollectionField::new('property')->setEntryType(PropertyType::class),
            TextField::new('value'),
        ];
    }
}
