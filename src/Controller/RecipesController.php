<?php

namespace App\Controller;

use App\Entity\Recipe;
use App\Entity\RecipeCategory;
use App\Form\SearchRecipeType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RecipesController extends AbstractController
{
    /**
     * @Route("/recipes", name="recipes")
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        $searchForm = $this->createForm(SearchRecipeType::class, null, [
            'method' => 'GET',
        ]);
        $searchForm->handleRequest($request);
        $recipes = [];

        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $filters = $searchForm->getData();
            $recipes = $this->getDoctrine()
                ->getRepository(Recipe::class)
                ->findByType($filters);
        } else {
            $recipes = $this->getDoctrine()
                ->getRepository(Recipe::class)
                ->findBy([], ['name' => 'ASC']);
        }

        $types = $this->getDoctrine()
            ->getRepository(RecipeCategory::class)
            ->findAll();

        return $this->render('recipes/index.html.twig', [
            'recipes' => $recipes,
            'types' => $types,
            'searchForm' => $searchForm->createView(),
        ]);
    }
}
