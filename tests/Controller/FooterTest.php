<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class FooterTest extends WebTestCase
{
    public function testFooterContactAndLegalLinks(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.footer a[href="tel:+33474711173"]');
        $this->assertSelectorExists('.footer a[href="mailto:josette.bourgogne@wanadoo.fr"]');
        $this->assertSelectorExists('.footer a[href="/legalMentions"]');
        $this->assertSelectorExists('.footer a.admin-link[href="/admin"][aria-label="Administration"]');
        $this->assertSelectorTextContains('.footer .copyright', '© '.date('Y').' Les Poteries de Josette');
        $this->assertSelectorExists('.footer .social a[aria-label="Facebook des Poteries de Josette"]');
        $this->assertSelectorExists('.footer .social a[aria-label="Instagram des Poteries de Josette"]');
    }
}
