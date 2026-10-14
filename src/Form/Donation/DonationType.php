<?php

namespace App\Form\Donation;

use App\Entity\Donation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class DonationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('amount', MoneyType::class, [
                'label' => 'Montant du don',
                'currency' => 'EUR',
                'divisor' => 100,
                'input' => 'integer',
                'empty_data' => null,
                'attr' => [
                    'placeholder' => '25,00',
                    'min' => 1,
                    'step' => 0.01,
                ],
            ])
            ->add('donorName', TextType::class, [
                'label' => 'Votre nom (optionnel)',
                'required' => false,
            ])
            ->add('donorEmail', EmailType::class, [
                'label' => 'Votre email (optionnel)',
                'required' => false,
            ])
            ->add('message', TextareaType::class, [
                'label' => 'Message (optionnel)',
                'required' => false,
                'attr' => [
                    'rows' => 3,
                    'placeholder' => 'Un petit mot pour le refuge...',
                ],
            ])
            ->add('cardNumber', TextType::class, [
                'label' => 'Numéro de carte',
                'mapped' => false,
                'attr' => [
                    'placeholder' => '4242 4242 4242 4242',
                    'autocomplete' => 'cc-number',
                    'inputmode' => 'numeric',
                ],
                'constraints' => [
                    new NotBlank(message: 'Veuillez saisir un numéro de carte.'),
                    new Regex(pattern: '/^[0-9 ]{12,23}$/', message: 'Le numéro de carte est invalide.'),
                ],
            ])
            ->add('cardExpiry', TextType::class, [
                'label' => 'Expiration (MM/AA)',
                'mapped' => false,
                'attr' => [
                    'placeholder' => '12/28',
                    'autocomplete' => 'cc-exp',
                ],
                'constraints' => [
                    new NotBlank(message: 'Veuillez saisir la date d\'expiration.'),
                    new Regex(pattern: '/^(0[1-9]|1[0-2])\/\d{2}$/', message: 'Format attendu : MM/AA.'),
                ],
            ])
            ->add('cardCvc', TextType::class, [
                'label' => 'CVC',
                'mapped' => false,
                'attr' => [
                    'placeholder' => '123',
                    'autocomplete' => 'cc-csc',
                    'inputmode' => 'numeric',
                ],
                'constraints' => [
                    new NotBlank(message: 'Veuillez saisir le code de sécurité.'),
                    new Regex(pattern: '/^\d{3,4}$/', message: 'Le code de sécurité est invalide.'),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Donation::class,
        ]);
    }
}
