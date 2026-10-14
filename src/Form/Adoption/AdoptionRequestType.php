<?php

namespace App\Form\Adoption;

use App\Entity\AdoptionRequest;
use App\Enum\HousingType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdoptionRequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('housing', EnumType::class, [
                'class' => HousingType::class,
                'label' => 'Type de logement',
                'choice_label' => static fn (HousingType $housing): string => $housing->label(),
            ])
            ->add('hasGarden', CheckboxType::class, [
                'label' => 'Je dispose d\'un jardin',
                'required' => false,
            ])
            ->add('hasOtherPets', CheckboxType::class, [
                'label' => 'J\'ai déjà des animaux à la maison',
                'required' => false,
            ])
            ->add('hasChildren', CheckboxType::class, [
                'label' => 'J\'ai des enfants',
                'required' => false,
            ])
            ->add('experience', TextareaType::class, [
                'label' => 'Votre expérience avec les animaux',
                'required' => false,
                'attr' => [
                    'rows' => 3,
                    'placeholder' => 'Animaux déjà eus, connaissance de l\'espèce...',
                ],
            ])
            ->add('message', TextareaType::class, [
                'label' => 'Votre message',
                'required' => false,
                'attr' => [
                    'rows' => 5,
                    'placeholder' => 'Parlez-nous de vous, de votre foyer et de votre motivation...',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => AdoptionRequest::class,
        ]);
    }
}
