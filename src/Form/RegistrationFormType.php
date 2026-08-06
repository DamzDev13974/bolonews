<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
/* rajout du use des Constraints avec l'alias Assert pour éviter de creer d'injecter les news un par un comme ceux faits par Symfo */
use Symfony\Component\Validator\Constraints as Assert;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email',EmailType::class, [
                'constraints' => [
                    new Assert\NotBlank(
                        message: 'Il faut saisir un email'
                    )
                ]
            ])
        
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'constraints' => [
                    new IsTrue(
                        message: 'Vous devez accepter les conditions',
                    ),
                ],
            ])
            ->add('plainPassword', PasswordType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank(
                        message: 'Il faut saisir un mot de passe',
                    ),
                    new Length(
                        min: 6,
                        minMessage: 'Le mot de passe doit avoir au minium {{ limit }} caractères',
                        // max length allowed by Symfony for security reasons
                        max: 4096,
                    ),
                ],
            ])
            ->add('photo', FileType::class, [
                "mapped"=> false,
                'constraints'=>[
                    new Assert\NotBlank(
                        message: 'Il faut importer une photo'
                    )
                ]
                ])
            ->add('nom', TextType::class,[
                'constraints'=> [
                    new Assert\Length(
                        min :2,
                        minMessage: 'le nom doit avoir minium {{limit}} caractères',
                        max :20,
                        maxMessage: 'le nom doit avoir maximum {{limit}} caractères'
                    )
                ]
            ])
            ->add('prenom', TextType::class,[
                'constraints'=> [
                    new Assert\Length(
                        min :2,
                        minMessage: 'le prenom doit avoir minium {{limit}} caractères',
                        max :20,
                        maxMessage: 'le prenom doit avoir maximum {{limit}} caractères'
                    )
                ]
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
