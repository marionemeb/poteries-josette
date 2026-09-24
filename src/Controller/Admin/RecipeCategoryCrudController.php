<?php

namespace App\Controller\Admin;

use App\Entity\RecipeCategory;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class RecipeCategoryCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return RecipeCategory::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Catégorie de recettes')
            ->setEntityLabelInPlural('Catégories de recettes')
            ->setPageTitle(Crud::PAGE_INDEX, 'Catégories');
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('name', 'Nom');
    }
}
