<?php

namespace App\Command;

use App\Entity\Blog;
use App\Entity\BlogType;
use App\Entity\Event;
use App\Entity\Product;
use App\Entity\Recipe;
use App\Entity\RecipeCategory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Dev-only helper so the site shows something when browsing it locally —
 * no fixtures bundle is installed (see the project skill: Composer 1 can no
 * longer resolve new packages). Refuses to run outside dev/test so it can
 * never touch a real environment by accident, and is a no-op if data
 * already exists so re-running bin/dev-setup.sh stays safe.
 */
class LoadFakeDataCommand extends Command
{
    protected static $defaultName = 'app:load-fake-data';

    private EntityManagerInterface $em;
    private KernelInterface $kernel;

    public function __construct(EntityManagerInterface $em, KernelInterface $kernel)
    {
        parent::__construct();
        $this->em = $em;
        $this->kernel = $kernel;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $environment = $this->kernel->getEnvironment();
        if (!in_array($environment, ['dev', 'test'], true)) {
            $output->writeln('<error>Refusé : cette commande ne tourne qu\'en dev/test, pas en '.$environment.'.</error>');

            return 1;
        }

        if ($this->em->getRepository(Product::class)->findOneBy([]) !== null) {
            $output->writeln('Des données existent déjà, rien à faire.');

            return 0;
        }

        $product1 = (new Product())
            ->setTitle('Vase émaillé bleu')
            ->setName('vase-emaille-bleu')
            ->setDescription('Pièce unique tournée et émaillée à la main, inspirée des bleus du Beaujolais.')
            ->setUpdatedAt(new \DateTime());
        $this->em->persist($product1);

        $product2 = (new Product())
            ->setTitle('Bol en grès')
            ->setName('bol-en-gres')
            ->setDescription('Bol quotidien en grès chamotté, émail mat.')
            ->setUpdatedAt(new \DateTime());
        $this->em->persist($product2);

        $event1 = (new Event())
            ->setTitle('Marché de potiers de Oingt')
            ->setDescription('Exposition annuelle des artisans du village.')
            ->setLocation('Oingt')
            ->setDateStart(new \DateTime('+2 weeks'))
            ->setDateEnd(new \DateTime('+2 weeks +2 days'))
            ->setUpdatedAt(new \DateTime());
        $this->em->persist($event1);

        $blogType = (new BlogType())->setName('Céramistes amis');
        $this->em->persist($blogType);
        $blog1 = (new Blog())
            ->setName('Atelier de Marie')
            ->setDescription('Une potière voisine dont le travail mérite le détour.')
            ->setType($blogType);
        $this->em->persist($blog1);

        $recipeCategory = (new RecipeCategory())->setName('Plats au four');
        $this->em->persist($recipeCategory);
        $recipe1 = (new Recipe())
            ->setName('Gratin en terrine émaillée')
            ->setDescription('Une recette pensée pour la terrine de Josette.')
            ->setIngredient('Pommes de terre, crème, fromage')
            ->setUpdatedAt(new \DateTime())
            ->setCategory($recipeCategory);
        $this->em->persist($recipe1);

        $this->em->flush();

        $output->writeln('Fausses données insérées.');

        return 0;
    }
}
