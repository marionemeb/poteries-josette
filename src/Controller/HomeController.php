<?php

namespace App\Controller;

use App\Repository\EventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(EventRepository $events): Response
    {
        // The "next event" band is a bonus: a database problem must never
        // take the home page down with it (same rule as NavigationExtension).
        try {
            $nextEvent = $events->findUpcoming()[0] ?? null;
        } catch (\Throwable $e) {
            $nextEvent = null;
        }

        return $this->render('home/index.html.twig', [
            'next_event' => $nextEvent,
        ]);
    }
}
