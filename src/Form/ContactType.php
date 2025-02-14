<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\OptionsResolver\OptionsResolver;


class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le nom est requis']),
                    new Assert\Length(['max' => 100, 'maxMessage' => 'Le nom ne peut pas dépasser 100 caractères']),
                ],
                'attr' => ['class' => 'mt-1 block w-full border border-gray-300 rounded-md shadow-sm'],
            ])
            ->add('firstname', TextType::class, [ // Nouveau champ
                'label' => 'Prénom',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le prénom est requis']),
                    new Assert\Length(['max' => 100, 'maxMessage' => 'Le prénom ne peut pas dépasser 100 caractères']),
                ],
                'attr' => ['class' => 'mt-1 block w-full border border-gray-300 rounded-md shadow-sm'],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'L\'email est requis']),
                    new Assert\Email(['message' => 'Veuillez entrer un email valide']),
                ],
                'attr' => ['class' => 'mt-1 block w-full border border-gray-300 rounded-md shadow-sm'],
            ])
            ->add('message', TextareaType::class, [
                'label' => 'Message',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le message est requis']),
                    new Assert\Length(['max' => 1000, 'maxMessage' => 'Le message ne peut pas dépasser 1000 caractères']),
                ],
                'attr' => ['class' => 'mt-1 block w-full border border-gray-300 rounded-md shadow-sm', 'rows' => 5],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Envoyer',
                'attr' => ['class' => 'px-6 py-2 bg-blue-600 text-white font-semibold rounded-md hover:bg-blue-700 transition'],
            ]);
    }
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => true,  // Active la protection CSRF
            'csrf_field_name' => '_token',  // Nom du champ CSRF (par défaut)
            'csrf_token_id'   => 'contact_form', // Identifiant unique du token
        ]);
    }
}
