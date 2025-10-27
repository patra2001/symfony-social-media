<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class UserProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'label_attr' => ['class' => 'form-label'],
                'disabled' => true,
                'attr' => [
                    'class' => 'form-control',
                ],
            ])
            ->add('firstName', TextType::class, [
                'label' => 'First Name',
                'label_attr' => ['class' => 'form-label'],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Enter your first name',
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'First name is required']),
                    new Assert\Length([
                        'min' => 2,
                        'max' => 255,
                        'minMessage' => 'First name must be at least {{ limit }} characters',
                        'maxMessage' => 'First name cannot exceed {{ limit }} characters',
                    ]),
                ],
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Last Name',
                'label_attr' => ['class' => 'form-label'],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Enter your last name',
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Last name is required']),
                    new Assert\Length([
                        'min' => 2,
                        'max' => 255,
                        'minMessage' => 'Last name must be at least {{ limit }} characters',
                        'maxMessage' => 'Last name cannot exceed {{ limit }} characters',
                    ]),
                ],
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false,
                'required' => true,
                'first_options' => [
                    'label' => 'Password',
                    'label_attr' => ['class' => 'form-label'],
                    'attr' => [
                        'class' => 'form-control',
                        'placeholder' => 'Enter new password',
                    ],
                ],
                'second_options' => [
                    'label' => 'Confirm Password',
                    'label_attr' => ['class' => 'form-label'],
                    'attr' => [
                        'class' => 'form-control',
                        'placeholder' => 'Re-enter password',
                    ],
                ],
                'invalid_message' => 'The password fields must match.',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Password is required']),
                    new Assert\Length([
                        'min' => 6,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                    ]),
                ],
            ])
            ->add('bio', TextareaType::class, [
                'label' => 'Bio',
                'label_attr' => ['class' => 'form-label'],
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Write something about yourself...',
                    'rows' => 4,
                ],
                'constraints' => [
                    new Assert\Length([
                        'max' => 500,
                        'maxMessage' => 'Bio cannot exceed {{ limit }} characters',
                    ]),
                ],
            ])
            ->add('profileImage', FileType::class, [
                'label' => 'Profile Image (optional)',
                'label_attr' => ['class' => 'form-label'],
                'required' => false,
                'mapped' => false,
                'attr' => [
                    'class' => 'form-control',
                    'accept' => 'image/*',
                ],
                'constraints' => [
                    new Assert\File([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/gif',
                            'image/webp',
                        ],
                        'mimeTypesMessage' => 'Only image files are allowed (JPEG, PNG, GIF, WebP)',
                    ]),
                ],
            ])
            // ->add('submit', SubmitType::class, [
            //     'label' => 'Save Changes',
            //     'attr' => [
            //         'class' => 'btn btn-primary',
            //     ],
            // ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}