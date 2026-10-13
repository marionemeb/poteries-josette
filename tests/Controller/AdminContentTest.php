<?php

namespace App\Tests\Controller;

use App\Entity\Blog;
use App\Entity\BlogType;
use App\Entity\Event;
use App\Entity\Product;
use App\Entity\Recipe;
use App\Entity\RecipeCategory;
use App\Entity\User;
use App\Tests\DatabaseWebTestCase;

/**
 * What Josette does in the back office, end to end: she fills in a form,
 * and the result shows up (or disappears) on the public site.
 */
class AdminContentTest extends DatabaseWebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Child rows first: blog and recipe have foreign keys to their types.
        foreach ([Blog::class, BlogType::class, Recipe::class, RecipeCategory::class, Event::class, Product::class, User::class] as $entity) {
            $this->purge($entity);
        }

        $admin = (new User())
            ->setEmail('josette@poterie-josette.com')
            ->setRoles(['ROLE_ADMIN']);
        $admin->setPassword('unused');
        $this->persist($admin);
        $this->client->loginUser($admin);
    }

    public function testNewEventShowsOnTheEventsPageAndTheHomePage(): void
    {
        $start = new \DateTime('+10 days');
        $crawler = $this->client->request('GET', '/admin/event/new');
        $this->client->submit($crawler->filter('form[name="Event"]')->form([
            'Event[title]' => 'Marché de Noël',
            'Event[description]' => 'Poteries et coquelicots à offrir.',
            'Event[location]' => 'Oingt',
            'Event[dateStart]' => $start->format('Y-m-d'),
            'Event[dateEnd]' => (clone $start)->modify('+1 day')->format('Y-m-d'),
        ]));
        $this->assertResponseRedirects();

        $this->client->request('GET', '/events');
        $this->assertSelectorTextContains('body', 'Marché de Noël');

        $this->client->request('GET', '/');
        $this->assertSelectorTextContains('.home-event-title', 'Marché de Noël');
    }

    public function testNewRecipeShowsUnderItsCategory(): void
    {
        $category = (new RecipeCategory())->setName('Plats au four');
        $this->persist($category);

        $crawler = $this->client->request('GET', '/admin/recipe/new');
        $form = $crawler->filter('form[name="Recipe"]')->form([
            'Recipe[name]' => 'Tian de légumes',
            'Recipe[description]' => 'Cuit doucement dans un tian.',
            'Recipe[ingredient]' => 'Courgettes, tomates, oignons',
        ]);
        $form['Recipe[category]']->select((string) $category->getId());
        $this->client->submit($form);
        $this->assertResponseRedirects();

        $crawler = $this->client->request('GET', '/recipes');
        $this->assertSelectorTextContains('body', 'Tian de légumes');
        $filters = $crawler->filter('label.category-filter-chip')->each(fn ($node) => $node->text());
        $this->assertContains('Plats au four', $filters);
    }

    public function testNewFavouriteShowsOnTheBlogPage(): void
    {
        $type = (new BlogType())->setName('Artisans amis');
        $this->persist($type);

        $crawler = $this->client->request('GET', '/admin/blog/new');
        $form = $crawler->filter('form[name="Blog"]')->form([
            'Blog[name]' => 'La verrerie du village',
            'Blog[description]' => 'Des verres soufflés à deux pas de l’atelier.',
            'Blog[location]' => 'Oingt',
            'Blog[url]' => 'https://example.com',
        ]);
        $form['Blog[type]']->select((string) $type->getId());
        $this->client->submit($form);
        $this->assertResponseRedirects();

        $this->client->request('GET', '/blog');
        $this->assertSelectorTextContains('body', 'La verrerie du village');
    }

    public function testDeletedPotteryDisappearsFromTheSite(): void
    {
        $product = (new Product())->setTitle('Pichet bleu')->setUpdatedAt(new \DateTime());
        $this->persist($product);

        $crawler = $this->client->request('GET', '/admin/product');
        $token = $crawler->filter('#action-confirmation-form input[name="token"]')->attr('value');
        $this->client->request('POST', '/admin/product/'.$product->getId().'/delete', ['token' => $token]);
        $this->assertResponseRedirects();
        // A bad token also redirects, so check the row is really gone.
        $this->assertNull($this->entityManager->getRepository(Product::class)->find($product->getId()));

        $this->client->request('GET', '/articles');
        $this->assertSelectorTextNotContains('body', 'Pichet bleu');
    }

    public function testEventWithoutTitleIsRefused(): void
    {
        $crawler = $this->client->request('GET', '/admin/event/new');
        $this->client->submit($crawler->filter('form[name="Event"]')->form([
            'Event[title]' => '',
            'Event[dateStart]' => (new \DateTime('+1 day'))->format('Y-m-d'),
            'Event[dateEnd]' => (new \DateTime('+1 day'))->format('Y-m-d'),
        ]));

        // The form comes back with an error instead of saving.
        $this->assertResponseStatusCodeSame(422);
        $this->assertSame(0, $this->entityManager->getRepository(Event::class)->count([]));
    }

    public function testEventEndingBeforeItStartsIsRefused(): void
    {
        $crawler = $this->client->request('GET', '/admin/event/new');
        $this->client->submit($crawler->filter('form[name="Event"]')->form([
            'Event[title]' => 'Marché de Noël',
            'Event[description]' => 'Poteries à offrir.',
            'Event[location]' => 'Oingt',
            'Event[dateStart]' => (new \DateTime('+10 days'))->format('Y-m-d'),
            'Event[dateEnd]' => (new \DateTime('+9 days'))->format('Y-m-d'),
        ]));

        $this->assertResponseStatusCodeSame(422);
        $this->assertSelectorTextContains('body', 'La fin ne peut pas être avant le début.');
        $this->assertSame(0, $this->entityManager->getRepository(Event::class)->count([]));
    }
}
