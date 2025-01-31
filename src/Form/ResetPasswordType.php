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
        // Je construis le formulaire avec les champs nécessaires pour la réinitialisation du mot de passe.
        $builder
            ->add('password', PasswordType::class, [
                // J'ajoute un champ pour le nouveau mot de passe.
                'label' => 'Nouveau mot de passe',
                // J'applique une contrainte pour m'assurer que le champ n'est pas vide.
                'constraints' => [new NotBlank(['message' => 'Le mot de passe ne peut pas être vide'])],
            ])
            ->add('confirm_password', PasswordType::class, [
                // J'ajoute un champ pour confirmer le nouveau mot de passe.
                'label' => 'Confirmer le mot de passe',
                // J'applique une contrainte pour m'assurer que le champ de confirmation n'est pas vide.
                'constraints' => [new NotBlank(['message' => 'La confirmation ne peut pas être vide'])],
            ])
            ->add('submit', SubmitType::class, [
                // J'ajoute un bouton pour soumettre le formulaire.
                'label' => 'Réinitialiser',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        // Je configure les options par défaut pour ce formulaire.
        $resolver->setDefaults([]);
    }
}
