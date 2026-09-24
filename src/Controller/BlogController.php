<?php

namespace App\Controller;

use App\Form\SearchType;
use App\Repository\BlogRepository;
use App\Repository\BlogTypeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BlogController extends AbstractController
{
    #[Route('/blog', name: 'blog')]
    public function index(Request $request, BlogRepository $blogs, BlogTypeRepository $blogTypes): Response
    {
        $searchForm = $this->createForm(SearchType::class, null, [
            'method' => 'GET',
        ]);
        $searchForm->handleRequest($request);

        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $blogArticles = $blogs->findByType($searchForm->getData());
        } else {
            $blogArticles = $blogs->findBy([], null);
        }

        return $this->render('blog/index.html.twig', [
            'blogArticles' => $blogArticles,
            'types' => $blogTypes->findAll(),
            'searchForm' => $searchForm->createView(),
        ]);
    }
}
