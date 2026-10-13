<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SitemapController extends AbstractController
{
    /**
     * List of the public pages for search engines (declared in robots.txt).
     * Events, recipes and "coups de cœur" only appear when they have content,
     * like their links in the menu.
     */
    #[Route('/sitemap.xml', name: 'sitemap', defaults: ['_format' => 'xml'])]
    public function index(): Response
    {
        $response = $this->render('sitemap/index.xml.twig');
        $response->headers->set('Content-Type', 'application/xml; charset=UTF-8');

        return $response;
    }
}
