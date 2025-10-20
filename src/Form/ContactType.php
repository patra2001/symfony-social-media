<?php

namespace App\Form;

use App\Entity\Enquiry;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'required' => true,
                'label' => 'Name',
                'label_attr' => ['class' => 'form-label'],
                'attr' => ['class' => 'form-control', 'placeholder' => 'Your name'],
            ])
            ->add('email', EmailType::class, [
                'required' => true,
                'label' => 'Email',
                'label_attr' => ['class' => 'form-label'],
                'attr' => ['class' => 'form-control', 'placeholder' => 'you@example.com'],
            ])
            ->add('phone', TelType::class, [
                'required' => true,
                'label' => 'Phone',
                'label_attr' => ['class' => 'form-label'],
                'attr' => ['class' => 'form-control', 'placeholder' => '+555 555 5555'],
            ])
            ->add('profile', FileType::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'Profile (PDF or image file)',
                'label_attr' => ['class' => 'form-label'],
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'application/pdf',
                            'image/*',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid PDF or image file',
                    ])
                ],
            ])
            ->add('message', TextareaType::class, [
                'required' => false,
                'label' => 'Message',
                'label_attr' => ['class' => 'form-label'],
                'attr' => ['class' => 'form-control mb-4', 'rows' => 5, 'placeholder' => 'Write your message here'],
            ])
        ;
        
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Enquiry::class,
        ]);
    }
}
