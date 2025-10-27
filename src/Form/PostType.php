<?php

namespace App\Form;

use App\Entity\Post;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class PostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('content', TextareaType::class, [
                'label' => 'What\'s on your mind?',
                'label_attr' => ['class' => 'form-label'],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Share your thoughts...',
                    'rows' => 4,
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Post content cannot be empty']),
                    new Assert\Length([
                        'min' => 1,
                        'max' => 5000,
                        'minMessage' => 'Post must be at least 1 character',
                        'maxMessage' => 'Post cannot exceed 5000 characters',
                    ]),
                ],
            ])
            ->add('image', FileType::class, [
                'label' => 'Add an image (optional)',
                'label_attr' => ['class' => 'form-label'],
                'required' => false,
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
                'data_class' => null,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Post',
                'attr' => [
                    'class' => 'btn btn-primary',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Post::class,
        ]);
    }
}