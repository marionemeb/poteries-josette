<?php

namespace App\Tests\Controller;

use App\Entity\Product;
use App\Tests\DatabaseWebTestCase;

class ArticlesControllerTest extends DatabaseWebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->purge(Product::class);
    }

    public function testPageListsSeededProduct(): void
    {
        $product = (new Product())
            ->setTitle('Vase émaillé bleu')
            ->setDescription('Pièce unique tournée et émaillée à la main.')
            ->setUpdatedAt(new \DateTime());
        $this->persist($product);

        $this->client->request('GET', '/articles');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'Vase émaillé bleu');
    }

    public function testPageLoadsWithNoProducts(): void
    {
        $this->client->request('GET', '/articles');

        $this->assertResponseIsSuccessful();
    }
}
