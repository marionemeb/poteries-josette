<?php

namespace App\Tests\Twig;

use App\Repository\BlogRepository;
use App\Repository\EventRepository;
use App\Repository\RecipeRepository;
use App\Twig\NavigationExtension;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for the defensive behavior added after the 19/09/2026 production
 * outage: a DB error in these functions (e.g. a schema mismatch on a table
 * unrelated to the current page) must never bubble up and take down every
 * page, since they run on every request via the nav/footer.
 */
class NavigationExtensionTest extends TestCase
{
    public function testHasUpcomingEventsReturnsTrueWhenRepositoryThrows(): void
    {
        $events = $this->createMock(EventRepository::class);
        $events->method('findUpcoming')->willThrowException($this->dbalException());

        $extension = new NavigationExtension($events, $this->createMock(RecipeRepository::class), $this->createMock(BlogRepository::class));

        $this->assertTrue($extension->hasUpcomingEvents());
    }

    public function testHasRecipesReturnsTrueWhenRepositoryThrows(): void
    {
        $recipes = $this->createMock(RecipeRepository::class);
        $recipes->method('findOneBy')->willThrowException($this->dbalException());

        $extension = new NavigationExtension($this->createMock(EventRepository::class), $recipes, $this->createMock(BlogRepository::class));

        $this->assertTrue($extension->hasRecipes());
    }

    public function testHasBlogArticlesReturnsTrueWhenRepositoryThrows(): void
    {
        $blog = $this->createMock(BlogRepository::class);
        $blog->method('findOneBy')->willThrowException($this->dbalException());

        $extension = new NavigationExtension($this->createMock(EventRepository::class), $this->createMock(RecipeRepository::class), $blog);

        $this->assertTrue($extension->hasBlogArticles());
    }

    public function testHasUpcomingEventsReflectsRealResultWhenNoError(): void
    {
        $events = $this->createMock(EventRepository::class);
        $events->method('findUpcoming')->willReturn([]);

        $extension = new NavigationExtension($events, $this->createMock(RecipeRepository::class), $this->createMock(BlogRepository::class));

        $this->assertFalse($extension->hasUpcomingEvents());
    }

    /**
     * Stand-in for a real Doctrine\DBAL\Exception — the exact class doesn't
     * matter here since NavigationExtension catches \Throwable broadly.
     */
    private function dbalException(): \RuntimeException
    {
        return new \RuntimeException('SQLSTATE[42S22]: Column not found: 1054 Unknown column');
    }
}
