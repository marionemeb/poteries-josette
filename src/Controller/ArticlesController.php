<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ArticlesController extends AbstractController
{
    #[Route('/articles', name: 'articles')]
    public function index(ProductRepository $products): Response
    {
        return $this->render('articles/index.html.twig', [
            'products' => $products->findAll(),
        ]);
    }
}
