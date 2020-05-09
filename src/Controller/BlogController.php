<?php

namespace App\Controller;

use App\Repository\BlogRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BlogController extends AbstractController
{
    /**
     * @Route("/blog", name="blog")
     * @param BlogRepository $blogArticles
     * @return Response
     */
    public function index(BlogRepository $blogArticles)
    {
        $blogArticles = $blogArticles->findAll();
        return $this->render('blog/index.html.twig', [
            'blogArticles' => $blogArticles,
        ]);
    }
}
