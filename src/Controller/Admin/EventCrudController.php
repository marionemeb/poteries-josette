<?php

namespace App\Controller\Admin;

use App\Entity\Event;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class EventCrudController extends AbstractCrudController
{
    use ImageFieldsTrait;

    public static function getEntityFqcn(): string
    {
        return Event::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Évènement')
            ->setEntityLabelInPlural('Mes évènements')
            ->setPageTitle(Crud::PAGE_INDEX, 'Les évènements de Josette');
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('title', 'Titre');
        yield TextareaField::new('description', 'Description');
        yield TextField::new('location', 'Lieu');
        yield TextField::new('link', 'Lien');
        yield DateField::new('dateStart', 'Début');
        yield DateField::new('dateEnd', 'Fin');
        yield from $this->imageFields('imageName');
    }
}
