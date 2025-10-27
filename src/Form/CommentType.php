<?php

namespace App\Form;

use App\Entity\Comment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class CommentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('content', TextareaType::class, [
                'label' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Write a comment...',
                    'rows' => 2,
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Comment cannot be empty']),
                    new Assert\Length([
                        'min' => 1,
                        'max' => 1000,
                        'minMessage' => 'Comment must be at least 1 character',
                        'maxMessage' => 'Comment cannot exceed 1000 characters',
                    ]),
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Comment',
                'attr' => [
                    'class' => 'btn btn-sm btn-outline-primary',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Comment::class,
        ]);
    }
}