<?php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ResetPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('password', PasswordType::class, [
                'label' => 'Nouveau mot de passe',
                'constraints' => [new NotBlank(['message' => 'Le mot de passe ne peut pas être vide'])],
            ])
            ->add('confirm_password', PasswordType::class, [
                'label' => 'Confirmer le mot de passe',
                'constraints' => [new NotBlank(['message' => 'La confirmation ne peut pas être vide'])],
            ])
            ->add('submit', SubmitType::class, ['label' => 'Réinitialiser']);
    }
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
