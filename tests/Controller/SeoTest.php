<?php

namespace App\Tests\Controller;

use App\Entity\Event;
use App\Tests\DatabaseWebTestCase;

/**
 * What search engines read: sitemap, page titles/descriptions, canonical
 * URLs, the workshop's structured-data card, and pages kept out of results.
 */
class SeoTest extends DatabaseWebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->purge(Event::class);
    }

    public function testSitemapListsThePublicPagesOnly(): void
    {
        $this->client->request('GET', '/sitemap.xml');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/xml; charset=UTF-8');

        $xml = simplexml_load_string($this->client->getResponse()->getContent());
        $this->assertNotFalse($xml, 'Le sitemap doit être du XML valide.');
        $urls = array_map('strval', $xml->xpath('//*[local-name()="loc"]'));

        $this->assertContains('https://www.poterie-josette.com/', $urls);
        $this->assertContains('https://www.poterie-josette.com/articles', $urls);
        $this->assertContains('https://www.poterie-josette.com/shop', $urls);
        // No upcoming event: the page is hidden from the menu, so from the sitemap too.
        $this->assertNotContains('https://www.poterie-josette.com/events', $urls);
        $this->assertNotContains('https://www.poterie-josette.com/login', $urls);
    }

    public function testSitemapListsEventsWhenOneIsUpcoming(): void
    {
        $this->persist((new Event())
            ->setTitle('Marché de Noël')
            ->setDescription('Exposition.')
            ->setLocation('Oingt')
            ->setDateStart(new \DateTime('+1 week'))
            ->setDateEnd(new \DateTime('+1 week'))
            ->setUpdatedAt(new \DateTime()));

        $this->client->request('GET', '/sitemap.xml');

        $this->assertStringContainsString(
            '<loc>https://www.poterie-josette.com/events</loc>',
            $this->client->getResponse()->getContent()
        );
    }

    public function testPagesHaveTheirOwnTitleDescriptionAndCanonicalUrl(): void
    {
        $crawler = $this->client->request('GET', '/shop');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextSame('title', 'Mon atelier · Les Poteries de Josette');
        $this->assertStringContainsString('Oingt', $crawler->filter('meta[name="description"]')->attr('content'));
        $this->assertSame('https://www.poterie-josette.com/shop', $crawler->filter('link[rel="canonical"]')->attr('href'));
    }

    public function testCanonicalUrlLeavesOutTheCategoryFilter(): void
    {
        $crawler = $this->client->request('GET', '/blog?search[name]=1');

        $this->assertSame('https://www.poterie-josette.com/blog', $crawler->filter('link[rel="canonical"]')->attr('href'));
    }

    public function testHomeHasTheWorkshopCardForSearchEngines(): void
    {
        $crawler = $this->client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $card = json_decode($crawler->filter('script[type="application/ld+json"]')->text(), true);
        $this->assertSame('LocalBusiness', $card['@type']);
        $this->assertSame('Les Poteries de Josette', $card['name']);
        $this->assertSame('+33474711173', $card['telephone']);
        $this->assertSame('69620', $card['address']['postalCode']);
    }

    public function testLoginPageIsKeptOutOfSearchResults(): void
    {
        $crawler = $this->client->request('GET', '/login');

        $this->assertSame('noindex', $crawler->filter('meta[name="robots"]')->attr('content'));
    }

    public function testOldContactPageRedirectsToTheFooter(): void
    {
        $this->client->request('GET', '/contact');

        $this->assertResponseStatusCodeSame(301);
        $this->assertResponseRedirects('/#contact');
        $this->client->request('GET', '/');
        $this->assertSelectorExists('#contact');
    }

    public function testPublicPagesAreIndexable(): void
    {
        $crawler = $this->client->request('GET', '/articles');

        $this->assertSame(0, $crawler->filter('meta[name="robots"]')->count());
    }

    public function testSharedLinksGetATitleDescriptionAndPhoto(): void
    {
        $crawler = $this->client->request('GET', '/shop');

        $meta = fn (string $property) => $crawler->filter('meta[property="'.$property.'"]')->attr('content');
        $this->assertSame('Mon atelier · Les Poteries de Josette', $meta('og:title'));
        $this->assertStringContainsString('Oingt', $meta('og:description'));
        $this->assertSame('https://www.poterie-josette.com/shop', $meta('og:url'));
        $this->assertSame('https://www.poterie-josette.com/img/share/shop.jpg', $meta('og:image'));
        $this->assertFileExists(__DIR__.'/../../public/img/share/shop.jpg');
    }

    public function testPagesWithoutTheirOwnPhotoShareTheHomePhoto(): void
    {
        $crawler = $this->client->request('GET', '/legalMentions');

        $this->assertSame(
            'https://www.poterie-josette.com/img/share/home.jpg',
            $crawler->filter('meta[property="og:image"]')->attr('content')
        );
    }

    public function testEverySharePhotoExists(): void
    {
        foreach (['home', 'articles', 'work', 'shop', 'events', 'recipes', 'blog'] as $page) {
            $file = __DIR__.'/../../public/img/share/'.$page.'.jpg';
            $this->assertFileExists($file);
            [$width, $height] = getimagesize($file);
            $this->assertSame([1200, 630], [$width, $height], $page.'.jpg doit faire 1200×630.');
        }
    }
}
