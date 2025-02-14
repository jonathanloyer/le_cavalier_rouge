<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MatchSheetType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Champ pour la référence de la feuille de match (consultation)
            ->add('reference', TextType::class, [
                'label' => 'Référence',
                'attr' => [
                    'placeholder' => 'Saisissez la référence de la feuille de match',
                    'class' => 'w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring focus:border-blue-300'
                ],
                'required' => false, // Optionnel pour la consultation
            ])

            // Champ de choix pour le type de feuille (création)
            ->add('typeFeuille', ChoiceType::class, [
                'label' => 'Type de feuille de match',
                'choices' => [
                    'Critériums' => 'criteriums',
                    'Nationale 2/3' => 'nationale_2_3',
                ],
                'expanded' => true, // Affiche les choix sous forme de boutons radio
                'multiple' => false,
                'required' => true,
            ])

            // Bouton de soumission pour la consultation
            ->add('consulter', SubmitType::class, [
                'label' => 'Consulter',
                'attr' => [
                    'class' => 'px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-500 transition',
                ],
            ])

            // Bouton de soumission pour la création
            ->add('creer', SubmitType::class, [
                'label' => 'Créer',
                'attr' => [
                    'class' => 'px-6 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-700 transition',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configurez les options du formulaire ici
            'data_class' => null, // Pas d'entité liée pour l'instant
            'csrf_protection' => true, // Activer la protection CSRF
        ]);
    }
}
