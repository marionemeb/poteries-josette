<?php

namespace App\Controller;

use App\Repository\RecipeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class RecipesController extends AbstractController
{
    /**
     * @Route("/recipes", name="recipes")
     */
    public function index(RecipeRepository $recipes)
    {
        $recipes = $recipes->findAll();
        return $this->render('recipes/index.html.twig', [
            'recipes' => $recipes,
        ]);
    }
}
