<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ContactControllerTest extends WebTestCase
{
    public function testSubmittingValidFormRedirectsBackToContact(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/contact');

        $form = $crawler->selectButton('Envoyer')->form([
            'contact[firstName]' => 'Marion',
            'contact[lastName]' => 'Bourgogne',
            'contact[email]' => 'marion@example.com',
            'contact[phone]' => '0474711173',
            'contact[message]' => 'Bonjour, je suis intéressée par une pièce vue sur le site.',
        ]);
        $client->submit($form);

        $this->assertResponseRedirects('/contact');
    }

    public function testSubmittingInvalidFormReRendersWithoutRedirecting(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/contact');

        $form = $crawler->selectButton('Envoyer')->form([
            'contact[firstName]' => 'Marion',
            'contact[lastName]' => 'Bourgogne',
            'contact[email]' => 'pas-un-email',
            'contact[phone]' => '0474711173',
            'contact[message]' => 'Bonjour',
        ]);
        $client->submit($form);

        // The controller only redirects on a valid submission — an invalid one
        // re-renders the same form (200), which is what stops the bad message
        // from ever reaching ContactController::notify().
        $this->assertResponseIsSuccessful();
    }
}
