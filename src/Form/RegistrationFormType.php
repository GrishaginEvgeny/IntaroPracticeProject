<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\RadioType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class RegistrationFormType extends AbstractType
{
    const INPUT_CORRECT_VALUE = 'Введите корректное значение';
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email')
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'You should agree to our terms.',
                    ]),
                ],
            ])
            ->add('plainPassword', PasswordType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a password',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
            ])
            ->add('lastname', TextType::class, [
                "mapped" => false,
                'label' => 'Фамилия',
                'required' => true,
                'help' => 'До 40 букв',
                'constraints' => [
                    new Length(max: 40, maxMessage: self::INPUT_CORRECT_VALUE),
                    new Regex('/^[А-ЯЁ][а-яё]+(-[А-ЯЁ])?[а-яё]+$/u', self::INPUT_CORRECT_VALUE)
                ],
            ])
            ->add('firstname', TextType::class, [
                "mapped" => false,
                'label' => 'Имя',
                'required' => true,
                'help' => 'До 40 букв',
                'constraints' => [
                    new Length(max: 40, maxMessage: self::INPUT_CORRECT_VALUE),
                ],
            ])
            ->add('patronymic', TextType::class, [
                "mapped" => false,
                'label' => 'Отчество',
                'required' => false,
                'help' => 'До 40 букв',
                'constraints' => [
                    new Length(max: 40, maxMessage: self::INPUT_CORRECT_VALUE),
                ],
            ])
            ->add('phone', TelType::class, [
                "mapped" => false,
                'label' => 'Номер телефона',
                'required' => false,
//                'constraints' => [
//                    new Regex('/^((8|\+7)[\- ]?)?(\(?\d{3}\)?[\- ]?)?[\d\- ]{7,10}$/')
//                ]
            ])
            ->add('birthdate', DateType::class, [
                "mapped" => false,
                'label' => 'Дата рождения',
                'required' => false,
                'years' => range(1940, 2018)
            ])
            // ->add('sex', RadioType::class,[
            //     "mapped" => false,
            //     'label' => 'Пол',
            //     'required' => false,
            ->add('sex', ChoiceType::class, [
                "mapped" => false,
                'label' => 'Пол',
                'choices' => [
                    'Женский' => 'Женский',
                    'Мужской' => 'Мужской',
                ],
            'expanded' => true
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
