<?php

namespace App\Form\Animal;

use App\Entity\MedicalRecord;
use App\Enum\MedicalRecordCategory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MedicalRecordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date', DateType::class, [
                'label' => 'Date',
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
            ])
            ->add('category', EnumType::class, [
                'class' => MedicalRecordCategory::class,
                'label' => 'Type d\'acte',
                'choice_label' => static fn (MedicalRecordCategory $category): string => $category->label(),
            ])
            ->add('title', TextType::class, [
                'label' => 'Motif',
                'attr' => ['placeholder' => 'Ex. Vaccination antirabique'],
            ])
            ->add('veterinarian', TextType::class, [
                'label' => 'Vétérinaire',
                'required' => false,
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Détails',
                'required' => false,
                'attr' => ['rows' => 4],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => MedicalRecord::class,
        ]);
    }
}
