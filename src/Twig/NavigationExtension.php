<?php

namespace App\Twig;

use App\Repository\BlogRepository;
use App\Repository\EventRepository;
use App\Repository\RecipeRepository;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class NavigationExtension extends AbstractExtension
{
    /** @var EventRepository */
    private $events;
    /** @var RecipeRepository */
    private $recipes;
    /** @var BlogRepository */
    private $blog;

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
        // These three functions run on every single page via the nav/footer
        // (see _navbar.html.twig / _footer.html.twig) — a DB error here must
        // never take down pages that have nothing to do with events/recipes/
        // blog. Defaulting to true just shows the (harmless) link; the actual
        // /events, /recipes or /blog page would still surface a real error
        // on its own if the underlying query is genuinely broken.
        try {
            return count($this->events->findUpcoming()) > 0;
        } catch (\Throwable $e) {
            return true;
        }
    }

    public function hasRecipes(): bool
    {
        try {
            return $this->recipes->findOneBy([]) !== null;
        } catch (\Throwable $e) {
            return true;
        }
    }

    public function hasBlogArticles(): bool
    {
        try {
            return $this->blog->findOneBy([]) !== null;
        } catch (\Throwable $e) {
            return true;
        }
    }
}
