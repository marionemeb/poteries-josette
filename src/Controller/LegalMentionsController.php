<?php


namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class LegalMentionsController extends AbstractController
{
    /**
     * @Route("/legalMentions", name="legalMentions")
     */
    public function index()
    {
        return $this->render('legalMentions/index.html.twig');
    }
}
