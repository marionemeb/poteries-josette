<?php

namespace App\Tests;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Base class for functional tests that need real rows in the test database
 * (no fixtures bundle is installed, so fixtures are built by hand per test).
 * Tables used by a test are purged in setUp() so each test starts from a
 * known, empty state regardless of what earlier tests left behind.
 *
 * createClient() itself boots the kernel, so it's called once here rather
 * than booting separately — calling both is deprecated since Symfony 4.4
 * ("kernel has been booted") and becomes a hard error in 5.0.
 */
abstract class DatabaseWebTestCase extends WebTestCase
{
    protected KernelBrowser $client;
    protected EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();
        $this->entityManager = self::$container->get('doctrine')->getManager();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->entityManager->close();
    }

    /**
     * @param class-string $entityClass
     */
    protected function purge(string $entityClass): void
    {
        $this->entityManager->createQuery('DELETE FROM '.$entityClass)->execute();
    }

    protected function persist(object $entity): void
    {
        $this->entityManager->persist($entity);
        $this->entityManager->flush();
    }
}
