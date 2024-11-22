<?php

namespace App\Form;

use App\Entity\Club;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;



class ProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('pseudo', TextType::class, [
            'attr' => ['placeholder' => 'Choisissez un nom d\'utilisateur'],
            'constraints' => [
                new NotBlank(['message' => 'Veuillez entrer un nom d\'utilisateur']),
                new Length([
                    'min' => 3,
                    'max' => 50,
                    'minMessage' => 'Votre pseudo doit comporter au moins {{ limit }} caractères',
                    'maxMessage' => 'Votre pseudo ne peut pas dépasser {{ limit }} caractères',
                ]),
            ],
        ])
        ->add('lastName', TextType::class, [
            'attr' => ['placeholder' => 'Choisissez un nom d\'utilisateur'],
            'constraints' => [
                new NotBlank(['message' => 'Veuillez entrer un nom d\'utilisateur']),
                new Length([
                    'min' => 3,
                    'max' => 50,
                    'minMessage' => 'Votre pseudo doit comporter au moins {{ limit }} caractères',
                    'maxMessage' => 'Votre pseudo ne peut pas dépasser {{ limit }} caractères',
                ]),
            ],
        ])
        ->add('firstName', TextType::class, [
            'attr' => ['placeholder' => 'Choisissez un nom d\'utilisateur'],
            'constraints' => [
                new NotBlank(['message' => 'Veuillez entrer un nom d\'utilisateur']),
                new Length([
                    'min' => 3,
                    'max' => 50,
                    'minMessage' => 'Votre pseudo doit comporter au moins {{ limit }} caractères',
                    'maxMessage' => 'Votre pseudo ne peut pas dépasser {{ limit }} caractères',
                ]),
            ],
        ])
->add('email', EmailType::class, [
            'label' => 'Email',
            'required' => true,
            'attr' => [
                'class' => 'form-control',
            ],
        ])
        ->add('password', PasswordType::class, [
            'label' => 'Mot de passe',
            'required' => true,
            'attr' => [
                'class' => 'form-control',
            ],
        ])
        
        // j'ajoute un champ avatar de type FileType
            ->add('avatar', FileType::class, [
            'label' => 'Avatar (PNG)',

            // unmapped signifie que ce champ n'est associé à aucune propriété d'entité
            'mapped' => false,

            // je le  rends facultatif pour ne pas avoir à télécharger à nouveau le fichier PDF

            // chaque fois que je modifies les détails de l'utilisateur
            'required' => false,

            // les champs unmapped ne peuvent pas définir de contraintes de validation
            // dans l'entité associée, je peux donc utiliser les classes de contraintes PHP
            'constraints' => [
                new File([
                    'maxSize' => '1024k',
                    'mimeTypes' => [
                        'image/png',
                    ],
                    'mimeTypesMessage' => 'Veuillez charger une image de format png.',
                ])
            ],
        ]);
        
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
