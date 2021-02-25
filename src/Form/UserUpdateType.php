<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserUpdateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstname', TextType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please, enter a firstname'
                    ]),
                    new Length([
                        'min' => 2,
                        'minMessage' => 'Please, enter at least {{ limit }} characters'
                    ])
                ]
            ])
            ->add('lastname', TextType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please, enter a lastname'
                    ]),
                    new Length([
                        'min' => 2,
                        'minMessage' => 'Please, enter at least {{ limit }} characters'
                    ])
                ]
            ])
            ->add('email', EmailType::class)
            ->add('avatar', FileType::class, [
                'label' => 'Change your avatar',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => 'image/*',
                        'mimeTypesMessage' => 'Please choose a valid image'
                    ])
                ],
            ])
            ->add('plainPassword', PasswordType::class, [
                'label' => 'Enter your password to save changes',
                'mapped' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter your password to save changes',
                    ])
                ],
            ]);
    }
    
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
