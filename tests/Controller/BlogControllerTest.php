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

    public function testPageLoadsWithNoArticles(): void
    {
        $this->client->request('GET', '/blog');

        $this->assertResponseIsSuccessful();
    }
}
