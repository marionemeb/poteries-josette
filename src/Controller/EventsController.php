<?php

namespace App\Controller;

use App\Repository\EventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EventsController extends AbstractController
{
    /**
     * @Route("/events", name="events")
     * @param EventRepository $events
     * @return Response
     */
    public function index(EventRepository $events)
    {
        $events = $events->findAll();
        return $this->render('events/index.html.twig', [
            'events' => $events,
        ]);
    }
}
