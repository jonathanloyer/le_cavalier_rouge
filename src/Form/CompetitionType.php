<?php

namespace App\Form;

use App\Entity\Competitions;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CompetitionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom de la compétition',
                'attr' => [
                    'class' => 'mt-1 block w-full p-2 border border-gray-300 rounded-md',
                    'placeholder' => 'Nom de la compétition',
                ],
                'required' => true,
            ])
            ->add('competitionDate', DateType::class, [
                'label' => 'Date de la compétition',
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'mt-1 block w-full p-2 border border-gray-300 rounded-md',
                ],
                'required' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Competitions::class,
        ]);
    }
}
