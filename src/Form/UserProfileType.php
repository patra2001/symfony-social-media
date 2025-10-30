<?php

namespace App\Form;

use App\Entity\User;
use App\Repository\CityRepository;
use App\Repository\CountryRepository;
use App\Repository\StateRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class UserProfileType extends AbstractType
{
    public function __construct(
        private readonly CountryRepository $countryRepository,
        private readonly StateRepository $stateRepository,
        private readonly CityRepository $cityRepository,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $countries = $this->getLocationChoices($this->countryRepository);
        $states = $this->getLocationChoices($this->stateRepository);
        $cities = $this->getLocationChoices($this->cityRepository);

        $user = $options['data'] ?? null;

        if ($user instanceof User) {
            $countries = $this->includeCurrentValue($countries, $user->getCountry());
            $states = $this->includeCurrentValue($states, $user->getState());
            $cities = $this->includeCurrentValue($cities, $user->getCity());
        }

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
            ->add('country', ChoiceType::class, [
                'label' => 'Country',
                'label_attr' => ['class' => 'form-label'],
                'placeholder' => 'Select your country',
                'choices' => $countries,
                'required' => false,
                'attr' => [
                    'class' => 'form-select',
                ],
            ])
            ->add('state', ChoiceType::class, [
                'label' => 'State',
                'label_attr' => ['class' => 'form-label'],
                'placeholder' => 'Select your state',
                'choices' => $states,
                'required' => false,
                'attr' => [
                    'class' => 'form-select',
                ],
            ])
            ->add('city', ChoiceType::class, [
                'label' => 'City',
                'label_attr' => ['class' => 'form-label'],
                'placeholder' => 'Select your city',
                'choices' => $cities,
                'required' => false,
                'attr' => [
                    'class' => 'form-select',
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
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }

    private function includeCurrentValue(array $choices, ?string $value): array
    {
        if ($value !== null && $value !== '' && !array_key_exists($value, $choices)) {
            $choices[$value] = $value;
        }

        return $choices;
    }

    private function getLocationChoices(ServiceEntityRepository $repository, string $fieldName = 'name'): array
    {
        $alias = 'choice_value';
        $rows = $repository->createQueryBuilder('entity')
            ->select(sprintf('entity.%s AS %s', $fieldName, $alias))
            ->orderBy(sprintf('entity.%s', $fieldName), 'ASC')
            ->getQuery()
            ->getScalarResult();

        $names = array_column($rows, $alias);

        return $names !== [] ? array_combine($names, $names) : [];
    }
}
