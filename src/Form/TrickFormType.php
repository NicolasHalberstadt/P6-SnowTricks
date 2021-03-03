<?php

namespace App\Form;

use App\Entity\TrickGroup;
use App\Entity\Trick;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class TrickFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'You have to enter a name for the trick'
                    ]),
                    new Length([
                        'min' => 2,
                        'minMessage' => 'Please, enter at least {{ limit }} characters'
                    ])
                ]
            ])
            ->add('description', TextareaType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'You have to enter a description'
                    ]),
                    new Length([
                        'min' => 5,
                        'minMessage' => 'Please, enter at least {{ limit }} characters'
                    ])
                ]
            ])
            ->add('images', FileType::class, [
                'label' => 'Add some images',
                'multiple' => true,
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'class' => 'form-control'
                ],
            ])
            ->add('videos', CollectionType::class, [
                'entry_type' => VideoType::class,
                'label' => 'Add a video',
                'mapped' => false,
                'required' => false,
                'prototype' => true,
                'allow_add' => true,
            ])
            ->add('trickGroup', EntityType::class, [
                'label' => 'Group',
                'class' => TrickGroup::class,
                'choice_label' => 'name',
                'required' => true,
            ]);
    }
    
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Trick::class,
        ]);
    }
}
