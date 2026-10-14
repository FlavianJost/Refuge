<?php

namespace App\Form\Review;

use App\Entity\Review;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReviewType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('rating', ChoiceType::class, [
                'label' => 'Votre note',
                'choices' => [
                    '★★★★★ (5/5)' => 5,
                    '★★★★ (4/5)' => 4,
                    '★★★ (3/5)' => 3,
                    '★★ (2/5)' => 2,
                    '★ (1/5)' => 1,
                ],
                'expanded' => true,
                'multiple' => false,
            ])
            ->add('comment', TextareaType::class, [
                'label' => 'Votre avis',
                'attr' => [
                    'rows' => 5,
                    'placeholder' => 'Partagez votre expérience avec le refuge...',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Review::class,
        ]);
    }
}
