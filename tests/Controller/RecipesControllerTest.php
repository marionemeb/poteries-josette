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

    public function testRecipeReactMountPointCarriesCorrectData(): void
    {
        // templates/recipes/index.html.twig mounts a React component (Recipes.jsx)
        // on a div.react element, passing its content through data-* attributes.
        // PHPUnit can't execute the JS that reads them, but it can catch the class
        // of regression that matters most here: the front-end dependency bumps
        // breaking the build, or a template change losing this data — either way
        // the recipe would silently stop being displayable client-side.
        $recipe = (new Recipe())
            ->setName('Tourte aux champignons')
            ->setDescription('Une recette de saison')
            ->setIngredient('Champignons, pate feuilletee, creme')
            ->setImageName('tourte.jpg')
            ->setUpdatedAt(new \DateTime());
        $this->persist($recipe);

        $crawler = $this->client->request('GET', '/recipes');

        $this->assertResponseIsSuccessful();
        $mountPoint = $crawler->filter('div.react');
        $this->assertGreaterThan(0, $mountPoint->count(), 'Le point de montage React (div.react) est introuvable.');
        $this->assertSame('Tourte aux champignons', $mountPoint->attr('data-name'));
        $this->assertSame('Une recette de saison', $mountPoint->attr('data-description'));
        $this->assertSame('Champignons, pate feuilletee, creme', $mountPoint->attr('data-ingredient'));
        $this->assertSame('tourte.jpg', $mountPoint->attr('data-image-name'));
    }

    public function testFilteringByCategoryOnlyShowsMatchingRecipes(): void
    {
        $matching = (new RecipeCategory())->setName('Plats au four');
        $other = (new RecipeCategory())->setName('Desserts');
        $this->persist($matching);
        $this->persist($other);

        $inCategory = (new Recipe())
            ->setName('Gratin dauphinois')
            ->setDescription('Un classique.')
            ->setIngredient('Pommes de terre, creme')
            ->setUpdatedAt(new \DateTime())
            ->setCategory($matching);
        $this->persist($inCategory);

        $outsideCategory = (new Recipe())
            ->setName('Tarte au citron')
            ->setDescription('Acidulee.')
            ->setIngredient('Citron, sucre, oeufs')
            ->setUpdatedAt(new \DateTime())
            ->setCategory($other);
        $this->persist($outsideCategory);

        $crawler = $this->client->request('GET', '/recipes');
        $selectName = $crawler->filter('select.searchTerm')->attr('name');
        $form = $crawler->filter('form.search')->form();
        $form[$selectName] = (string) $matching->getId();

        $this->client->submit($form);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'Gratin dauphinois');
        $this->assertSelectorTextNotContains('body', 'Tarte au citron');
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
