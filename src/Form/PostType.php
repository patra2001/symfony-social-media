<?php

namespace App\Form;

use App\Entity\Post;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
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
            ->add('type', ChoiceType::class, [
                'label' => 'Post Type',
                'label_attr' => ['class' => 'form-label'],
                'placeholder' => 'Select post type',
                'choices' => [
                    'Text Post' => Post::TYPE_TEXT,
                    'Image Post' => Post::TYPE_IMAGE,
                    'Video Post' => Post::TYPE_VIDEO,
                    'Link Post' => Post::TYPE_LINK,
                    'Poll Post' => Post::TYPE_POLL,
                ],
                'attr' => [
                    'class' => 'form-select',
                    'id' => 'post_type',
                    'required' => true,
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Please choose a post type']),
                    new Assert\Choice([
                        'choices' => Post::TYPE_CHOICES,
                        'message' => 'Select a valid post type',
                    ]),
                ],
            ])
            ->add('content', TextareaType::class, [
                'label' => 'What\'s on your mind?',
                'label_attr' => ['class' => 'form-label'],
                'attr' => [
                    'class' => 'form-control tinymce',
                    'id' => 'post_content',
                    'placeholder' => 'Share your thoughts...',
                    'rows' => 4,
                    'required' => true,
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
                // 'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'id' => 'post_image',
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
            ->add('extraData', HiddenType::class, [
                'mapped' => true,
                'required' => false,
                'attr' => [
                    'id' => 'post_extraData'
                ],
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