<?php

namespace App\Form;

use App\Entity\Club;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Je construis le formulaire avec les champs nécessaires.
        $builder
            // J’ajoute un champ pour l’email, avec le type EmailType.
            ->add('email', EmailType::class, [
                'label' => 'Adresse Email', // J’affiche un label "Adresse Email".
            ])
            // J’ajoute un champ pour le pseudo, avec le type TextType.
            ->add('pseudo', TextType::class, [
                'label' => 'Pseudo', // J’affiche un label "Pseudo".
            ])
            // J’ajoute un champ pour le nom, avec le type TextType.
            ->add('lastName', TextType::class, [
                'label' => 'Nom', // J’affiche un label "Nom".
            ])
            // J’ajoute un champ pour le prénom, avec le type TextType.
            ->add('firstName', TextType::class, [
                'label' => 'Prénom', // J’affiche un label "Prénom".
            ])
            // J’ajoute un champ pour sélectionner un club, avec le type EntityType.
            ->add('club', EntityType::class, [
                'class' => Club::class, // Je lie ce champ à l’entité Club.
                'choice_label' => 'name', // J’utilise le nom du club comme étiquette.
                'label' => 'Club', // J’affiche un label "Club".
            ])
            // J’ajoute une case à cocher pour l’état actif, avec le type CheckboxType.
            ->add('active', CheckboxType::class, [
                'label' => 'Actif', // J’affiche un label "Actif".
                'required' => false, // Le champ n’est pas obligatoire.
            ])
            // J’ajoute un bouton de soumission, avec le type SubmitType.
            ->add('save', SubmitType::class, [
                'label' => 'Enregistrer', // J’affiche un label "Enregistrer".
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        // Je configure les options pour ce formulaire.
        $resolver->setDefaults([
            'data_class' => User::class, // Je lie ce formulaire à l’entité User.
        ]);
    }
}
