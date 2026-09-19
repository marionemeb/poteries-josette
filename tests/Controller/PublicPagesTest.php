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
}
