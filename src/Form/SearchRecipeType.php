<?php

namespace App\Form;

use App\Entity\RecipeCategory;
use App\Repository\RecipeCategoryRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchRecipeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', EntityType::class,[
                'required' => false,
                'class' => RecipeCategory::class,
                'placeholder' => 'Toutes',
                // Radio buttons, rendered as pills by templates/_category_filter.html.twig
                'expanded' => true,
                'query_builder' => function (RecipeCategoryRepository $repository) {
                    return $repository->createQueryBuilder('c')
                        ->innerJoin('c.recipes', 'r')
                        ->groupBy('c.id');
                },
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => null,
        ]);
    }
}
