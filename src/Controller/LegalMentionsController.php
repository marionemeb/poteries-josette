<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LegalMentionsController extends AbstractController
{
    #[Route('/legalMentions', name: 'legalMentions')]
    public function index(): Response
    {
        return $this->render('legalMentions/index.html.twig');
    }
}
