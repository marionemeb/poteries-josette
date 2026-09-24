<?php

namespace App\Controller\Admin;

use App\Entity\Recipe;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class RecipeCrudController extends AbstractCrudController
{
    use ImageFieldsTrait;

    public static function getEntityFqcn(): string
    {
        return Recipe::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Recette')
            ->setEntityLabelInPlural('Mes recettes')
            ->setPageTitle(Crud::PAGE_INDEX, 'Les recettes de Josette');
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('name', 'Nom');
        yield AssociationField::new('category', 'Catégorie');
        yield TextareaField::new('description', 'Description');
        yield TextareaField::new('ingredient', 'Ingrédients')
            ->setHelp('Attention, les ingrédients doivent être séparés par une virgule');
        yield from $this->imageFields('imageName');
    }
}
