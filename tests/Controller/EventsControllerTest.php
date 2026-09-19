<?php

namespace App\Tests\Controller;

use App\Entity\Event;
use App\Tests\DatabaseWebTestCase;

class EventsControllerTest extends DatabaseWebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->purge(Event::class);
    }

    public function testPageListsSeededEvent(): void
    {
        $event = (new Event())
            ->setTitle('Marché de potiers de Oingt')
            ->setDescription('Exposition annuelle des artisans du village.')
            ->setLocation('Oingt')
            ->setDateStart(new \DateTime('2026-10-01'))
            ->setDateEnd(new \DateTime('2026-10-03'))
            ->setUpdatedAt(new \DateTime());
        $this->persist($event);

        $this->client->request('GET', '/events');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'Marché de potiers de Oingt');
    }

    public function testPageLoadsWithNoEvents(): void
    {
        $this->client->request('GET', '/events');

        $this->assertResponseIsSuccessful();
    }

    public function testPastEventIsExcludedFromTheListing(): void
    {
        $pastEvent = (new Event())
            ->setTitle('Marché déjà passé')
            ->setDescription('Exposition qui a déjà eu lieu.')
            ->setLocation('Oingt')
            ->setDateStart(new \DateTime('2020-01-01'))
            ->setDateEnd(new \DateTime('2020-01-02'))
            ->setUpdatedAt(new \DateTime());
        $this->persist($pastEvent);

        $this->client->request('GET', '/events');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextNotContains('body', 'Marché déjà passé');
    }

    public function testNavAndFooterHideEventsLinkWhenNoUpcomingEvents(): void
    {
        // setUp() already purged Event, so there is nothing upcoming here.
        $crawler = $this->client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSame(0, $crawler->filter('a[href="/events"]')->count());
    }

    public function testNavShowsEventsLinkWhenAnUpcomingEventExists(): void
    {
        $event = (new Event())
            ->setTitle('Marché à venir')
            ->setDescription('Prochaine exposition.')
            ->setLocation('Oingt')
            ->setDateStart(new \DateTime('+1 week'))
            ->setDateEnd(new \DateTime('+1 week +1 day'))
            ->setUpdatedAt(new \DateTime());
        $this->persist($event);

        $crawler = $this->client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertGreaterThan(0, $crawler->filter('a[href="/events"]')->count());
    }
}
