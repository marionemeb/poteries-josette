<?php

namespace App\Controller\Admin;

use App\Entity\BlogType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class BlogTypeCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return BlogType::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Type de coup de coeur')
            ->setEntityLabelInPlural('Types de coups de coeur')
            ->setPageTitle(Crud::PAGE_INDEX, 'Types');
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('name', 'Nom');
    }
}
