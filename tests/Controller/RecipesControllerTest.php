<?php

namespace App\Tests\Controller;

use App\Entity\Recipe;
use App\Entity\RecipeCategory;
use App\Tests\DatabaseWebTestCase;

class RecipesControllerTest extends DatabaseWebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Child rows first: recipe.category_id has a foreign key to recipe_category.
        $this->purge(Recipe::class);
        $this->purge(RecipeCategory::class);
    }

    public function testPageListsSeededRecipe(): void
    {
        $category = (new RecipeCategory())->setName('Plats au four');
        $this->persist($category);

        $recipe = (new Recipe())
            ->setName('Gratin en terrine émaillée')
            ->setDescription('Une recette pensée pour la terrine de Josette.')
            ->setIngredient('Pommes de terre, crème, fromage')
            ->setUpdatedAt(new \DateTime())
            ->setCategory($category);
        $this->persist($recipe);

        $this->client->request('GET', '/recipes');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'Gratin en terrine émaillée');
    }

    public function testPageLoadsWithNoRecipes(): void
    {
        $this->client->request('GET', '/recipes');

        $this->assertResponseIsSuccessful();
    }

    public function testPdfExportOfSeededRecipe(): void
    {
        $recipe = (new Recipe())
            ->setName('Tarte cuite au four à bois')
            ->setDescription('Recette de saison.')
            ->setIngredient('Farine, beurre, fruits')
            ->setUpdatedAt(new \DateTime());
        $this->persist($recipe);

        $this->client->request('GET', '/pdf/'.$recipe->getId());

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/pdf');
    }
}
