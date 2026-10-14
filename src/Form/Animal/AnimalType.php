<?php

namespace App\Form\Animal;

use App\Entity\Animal;
use App\Enum\AnimalStatus;
use App\Enum\Species;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\File;

class AnimalType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom',
                'attr' => ['placeholder' => 'Ex. Rex'],
            ])
            ->add('species', EnumType::class, [
                'class' => Species::class,
                'label' => 'Espèce',
                'choice_label' => static fn (Species $species): string => $species->label(),
            ])
            ->add('breed', TextType::class, [
                'label' => 'Race',
                'required' => false,
            ])
            ->add('age', IntegerType::class, [
                'label' => 'Âge (années)',
                'required' => false,
                'attr' => ['min' => 0],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => ['rows' => 5],
            ])
            ->add('status', EnumType::class, [
                'class' => AnimalStatus::class,
                'label' => 'Situation',
                'choice_label' => static fn (AnimalStatus $status): string => $status->label(),
            ])
            ->add('photoFile', FileType::class, [
                'label' => 'Photo principale',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File(
                        maxSize: '4M',
                        mimeTypes: ['image/jpeg', 'image/png', 'image/webp'],
                        mimeTypesMessage: 'Merci de téléverser une image valide (JPEG, PNG ou WebP).',
                    ),
                ],
            ])
            ->add('photosFiles', FileType::class, [
                'label' => 'Ajouter des photos à la galerie',
                'mapped' => false,
                'required' => false,
                'multiple' => true,
                'attr' => ['accept' => 'image/*'],
                'constraints' => [
                    new All([
                        new File(
                            maxSize: '4M',
                            mimeTypes: ['image/jpeg', 'image/png', 'image/webp'],
                            mimeTypesMessage: 'Merci de téléverser une image valide (JPEG, PNG ou WebP).',
                        ),
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Animal::class,
        ]);
    }
}
