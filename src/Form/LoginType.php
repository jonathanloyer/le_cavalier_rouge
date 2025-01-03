<?php

namespace App\Form;

use App\Entity\Club;
use App\Entity\Role;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;


class LoginType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('pseudo', TextType::class, [
                'label' => 'Pseudo',
                'attr' => [
                    'placeholder' => 'Entrez vôtre pseudo',
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le pseudo est obligatoire']),
                    new Assert\Length([
                        'min' => 3,
                        'max' => 50,
                        'minMessage' => 'Le pseudo est trop court',
                        'maxMessage' => 'Le pseudo est trop long ',
                    ]),
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Adresse email',
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Veuillez entrer votre adresse email']),
                    new Assert\Email(['message' => 'Veuillez entrer une adresse email valide']),
                ],
                'attr' => ['class' => 'input-email']
            ])



            ->add('password', PasswordType::class, [
                'label' => 'Mot de passe',
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Veuillez entrer votre mot de passe']),
                    new Assert\Length([
                        'min' => 6,
                        'minMessage' => 'Le mot de passe doit contenir au moins 6 caractères'
                    ])
                ],
                'attr' => ['class' => 'input-password']
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Se connecter',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
