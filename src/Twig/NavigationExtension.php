<?php

namespace App\Twig;

use App\Repository\BlogRepository;
use App\Repository\EventRepository;
use App\Repository\RecipeRepository;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class NavigationExtension extends AbstractExtension
{
    private EventRepository $events;
    private RecipeRepository $recipes;
    private BlogRepository $blog;

    public function __construct(EventRepository $events, RecipeRepository $recipes, BlogRepository $blog)
    {
        $this->events = $events;
        $this->recipes = $recipes;
        $this->blog = $blog;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('has_upcoming_events', [$this, 'hasUpcomingEvents']),
            new TwigFunction('has_recipes', [$this, 'hasRecipes']),
            new TwigFunction('has_blog_articles', [$this, 'hasBlogArticles']),
        ];
    }

    public function hasUpcomingEvents(): bool
    {
        return count($this->events->findUpcoming()) > 0;
    }

    public function hasRecipes(): bool
    {
        return $this->recipes->findOneBy([]) !== null;
    }

    public function hasBlogArticles(): bool
    {
        return $this->blog->findOneBy([]) !== null;
    }
}
