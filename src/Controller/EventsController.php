<?php

namespace App\Controller;

use App\Repository\EventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EventsController extends AbstractController
{
    #[Route('/events', name: 'events')]
    public function index(EventRepository $events): Response
    {
        return $this->render('events/index.html.twig', [
            'events' => $events->findUpcoming(),
        ]);
    }
}
