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
}
