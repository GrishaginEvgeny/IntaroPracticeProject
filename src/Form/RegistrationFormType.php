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
            ->add('plainPassword', PasswordType::class, [
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a password',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
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
                'required' => false
            ])
            ->add('birthdate', DateType::class, [
                "mapped" => false,
                'label' => 'Дата рождения',
                'required' => false,
                'years' => range(2021, 1960)
            ])
            ->add('sex', ChoiceType::class, [
                "mapped" => false,
                'label' => 'Пол',
                'choices' => [
                    'Женский' => 'Жен.',
                    'Мужской' => 'Муж.',
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
