<?php

namespace App\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    public function index(): Response
    {
        // No dashboard page of its own: open straight on the pottery list.
        return $this->redirect($this->container->get(AdminUrlGenerator::class)
            ->setController(ProductCrudController::class)
            ->generateUrl());
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()->setTitle('Les Poteries de Josette');
    }

    public function configureCrud(): Crud
    {
        return parent::configureCrud()->setPaginatorPageSize(20);
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::section('Catégories');
        yield MenuItem::linkTo(ProductCrudController::class, 'Mes poteries', 'fa fa-user');
        yield MenuItem::linkTo(EventCrudController::class, 'Mes évènements', 'fa fa-calendar');
        yield MenuItem::linkTo(BlogCrudController::class, 'Coups de coeur', 'fa fa-heart');
        yield MenuItem::linkTo(RecipeCrudController::class, 'Mes recettes', 'fa fa-utensils');

        yield MenuItem::section('Configuration');
        yield MenuItem::linkTo(BlogTypeCrudController::class, 'Types de coups de coeur', 'fa fa-cog');
        yield MenuItem::linkTo(RecipeCategoryCrudController::class, 'Catégories de recettes', 'fa fa-cog');

        yield MenuItem::linkToRoute('Retour au site web', 'fa fa-reply', 'home');
    }
}
