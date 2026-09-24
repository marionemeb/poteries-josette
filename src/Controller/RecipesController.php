<?php

namespace App\Controller;

use App\Form\SearchRecipeType;
use App\Repository\RecipeCategoryRepository;
use App\Repository\RecipeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RecipesController extends AbstractController
{
    #[Route('/recipes', name: 'recipes')]
    public function index(Request $request, RecipeRepository $recipeRepository, RecipeCategoryRepository $categories): Response
    {
        $searchForm = $this->createForm(SearchRecipeType::class, null, [
            'method' => 'GET',
        ]);
        $searchForm->handleRequest($request);

        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $recipes = $recipeRepository->findByType($searchForm->getData());
        } else {
            $recipes = $recipeRepository->findBy([], null);
        }

        return $this->render('recipes/index.html.twig', [
            'recipes' => $recipes,
            'types' => $categories->findAll(),
            'searchForm' => $searchForm->createView(),
        ]);
    }
}
