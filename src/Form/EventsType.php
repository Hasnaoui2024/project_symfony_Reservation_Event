<?php

namespace App\Form;

use App\Entity\Events;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\PositiveOrZero;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class EventsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Le titre ne peut pas être vide.']),
                ],
            ])
            ->add('description', TextareaType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'La description ne peut pas être vide.']),
                ],
            ])
            ->add('date', DateTimeType::class, [
                'required' => true, // Rend le champ optionnel
                'constraints' => [
                    new GreaterThan('today', message: "La date doit être ultérieure à aujourd'hui.")
                ]
            ])
            ->add('lieu', TextType::class, [
                'required' => true, 
                'constraints' => [
                    new NotBlank(['message' => 'Le lieu ne peut pas être vide.']),
                ],
            ])
            ->add('prix', NumberType::class, [
                'required' => true,
                'constraints' => [
                    new PositiveOrZero(['message' => 'Le prix ne peut pas être négatif.']),
                ],
            ])
            
             ->add('devise', ChoiceType::class, [
        'choices' => [
            'EUR (€)' => 'EUR',
            'USD ($)' => 'USD',
            'MAD (MAD)' => 'MAD',
        ],
        'required' => true,
        'label' => 'Devise',
        'attr' => ['class' => 'form-control']
    ])

            ->add('nbrPlace', NumberType::class, [
                'required' => true, 
                'constraints' => [
                    new PositiveOrZero(['message' => 'Le nombre de places ne peut pas être négatif.']),
                ],
            ])
            ->add('image', FileType::class, [
                'label' => 'Image de l’événement',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => ['image/jpeg', 'image/png'],
                        'mimeTypesMessage' => 'Veuillez uploader une image valide (JPEG, PNG).',
                    ])
                ]
            ])
            ->add('canceled', CheckboxType::class, [
                'label' => 'Événement annulé',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Events::class,
        ]);
    }
}