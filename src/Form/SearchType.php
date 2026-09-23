<?php

namespace App\Form;

use App\Entity\BlogType;
use App\Repository\BlogTypeRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', EntityType::class,[
                'required' => false,
                'class' => BlogType::class,
                'placeholder' => 'Toutes',
                // Radio buttons, rendered as pills by templates/_category_filter.html.twig
                'expanded' => true,
                'query_builder' => function (BlogTypeRepository $repository) {
                    return $repository->createQueryBuilder('t')
                        ->innerJoin('t.blog', 'b')
                        ->groupBy('t.id');
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
