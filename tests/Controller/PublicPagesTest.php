<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Smoke tests for public pages that don't touch the database, so they can run
 * without fixtures/migrations. Pages backed by a repository (articles, blog,
 * recipes, events) are covered separately once a test database is seeded.
 */
class PublicPagesTest extends WebTestCase
{
    /**
     * @dataProvider provideDbFreeRoutes
     */
    public function testPageLoadsSuccessfully(string $url): void
    {
        $client = static::createClient();
        $client->request('GET', $url);

        $this->assertResponseIsSuccessful();
    }

    public function provideDbFreeRoutes(): iterable
    {
        yield 'home' => ['/'];
        yield 'work (portfolio)' => ['/work'];
        yield 'shop (atelier presentation, misleadingly named)' => ['/shop'];
        yield 'legal mentions' => ['/legalMentions'];
        yield 'login' => ['/login'];
    }

    public function testLegalMentionsStateTheVatExemption(): void
    {
        // Josette isn't subject to VAT (micro-entreprise, franchise en base):
        // the mention is mandatory once that's the case.
        $client = static::createClient();
        $client->request('GET', '/legalMentions');

        $this->assertSelectorTextContains('body', 'TVA non applicable, art. 293 B du CGI');
    }
}
