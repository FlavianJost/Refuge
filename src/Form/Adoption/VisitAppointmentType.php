<?php

namespace App\Form\Adoption;

use App\Entity\VisitAppointment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;

class VisitAppointmentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('proposedAt', DateTimeType::class, [
                'label' => 'Date et heure souhaitées',
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
                'constraints' => [
                    new GreaterThanOrEqual('now', message: 'Merci de proposer une date future.'),
                ],
            ])
            ->add('note', TextareaType::class, [
                'label' => 'Précisions (facultatif)',
                'required' => false,
                'attr' => [
                    'rows' => 2,
                    'placeholder' => 'Disponibilités, informations complémentaires...',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => VisitAppointment::class,
        ]);
    }
}
