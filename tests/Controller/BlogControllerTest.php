<?php

namespace App\Tests\Controller;

use App\Entity\Blog;
use App\Entity\BlogType;
use App\Tests\DatabaseWebTestCase;

class BlogControllerTest extends DatabaseWebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Child rows first: blog.type_id has a foreign key to blog_type.
        $this->purge(Blog::class);
        $this->purge(BlogType::class);
    }

    public function testPageListsSeededArticle(): void
    {
        $type = (new BlogType())->setName('Céramistes amis');
        $this->persist($type);

        $blogArticle = (new Blog())
            ->setName('Atelier de Marie')
            ->setDescription('Une potière voisine dont le travail mérite le détour.')
            ->setType($type);
        $this->persist($blogArticle);

        $this->client->request('GET', '/blog');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'Atelier de Marie');
    }

    public function testFilteringByTypeOnlyShowsMatchingArticles(): void
    {
        $matching = (new BlogType())->setName('Céramistes amis');
        $other = (new BlogType())->setName('Recommandations diverses');
        $this->persist($matching);
        $this->persist($other);

        $inType = (new Blog())
            ->setName('Atelier de Marie')
            ->setDescription('Une potière voisine dont le travail mérite le détour.')
            ->setType($matching);
        $this->persist($inType);

        $outsideType = (new Blog())
            ->setName('Ma boutique de laine préférée')
            ->setDescription('Rien à voir avec la céramique.')
            ->setType($other);
        $this->persist($outsideType);

        $crawler = $this->client->request('GET', '/blog');
        $radioName = $crawler->filter('input.category-filter-input')->attr('name');
        $form = $crawler->filter('form.search')->form();
        $form[$radioName]->select((string) $matching->getId());

        $this->client->submit($form);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'Atelier de Marie');
        $this->assertSelectorTextNotContains('body', 'Ma boutique de laine préférée');
    }

    public function testEmptyTypeIsExcludedFromTheFilter(): void
    {
        $withArticle = (new BlogType())->setName('Céramistes amis');
        $empty = (new BlogType())->setName('Type sans article');
        $this->persist($withArticle);
        $this->persist($empty);

        $blogArticle = (new Blog())
            ->setName('Atelier de Marie')
            ->setDescription('Une potière voisine dont le travail mérite le détour.')
            ->setType($withArticle);
        $this->persist($blogArticle);

        $crawler = $this->client->request('GET', '/blog');

        $this->assertResponseIsSuccessful();
        $options = $crawler->filter('label.category-filter-chip')->each(fn ($node) => $node->text());
        $this->assertContains('Céramistes amis', $options);
        $this->assertNotContains('Type sans article', $options);
    }

    public function testPageLoadsWithNoArticles(): void
    {
        $this->client->request('GET', '/blog');

        $this->assertResponseIsSuccessful();
    }

    public function testNavAndFooterHideBlogLinkWhenNoArticles(): void
    {
        // setUp() already purged Blog/BlogType.
        $crawler = $this->client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSame(0, $crawler->filter('a[href="/blog"]')->count());
    }

    public function testNavShowsBlogLinkWhenAnArticleExists(): void
    {
        $blogArticle = (new Blog())
            ->setName('Atelier de Marie')
            ->setDescription('Une potière voisine dont le travail mérite le détour.');
        $this->persist($blogArticle);

        $crawler = $this->client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertGreaterThan(0, $crawler->filter('a[href="/blog"]')->count());
    }
}
