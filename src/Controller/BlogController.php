<?php

namespace App\Controller;

use App\Entity\Blog;
use App\Entity\BlogType;
use App\Form\SearchType;
use App\Repository\BlogRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\FormTypeInterface;

class BlogController extends AbstractController
{
    /**
     * @Route("/blog", name="blog")
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        //All articles of Blog
//        $blogArticles = $repoBlog->findAll();

        $searchForm = $this->createForm(SearchType::class, null, [
            'method' => 'GET',
        ]);
        $searchForm->handleRequest($request);
        $blogArticles = [];

        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $filters = $searchForm->getData();
            $blogArticles = $this->getDoctrine()
                ->getRepository(Blog::class)
                ->findByType($filters);
        } else {
            $blogArticles = $this->getDoctrine()
                ->getRepository(Blog::class)
                ->findBy([], null);
        }

        $types = $this->getDoctrine()
            ->getRepository(BlogType::class)
            ->findAll();

        return $this->render('blog/index.html.twig', [
            'blogArticles' => $blogArticles,
            'types' => $types,
            'searchForm' => $searchForm->createView(),
        ]);
    }
}
