<?php

namespace App\Form;

use App\Entity\Categorie;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class CategorieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('libelle', TextType::class,[
                'constraints'=>[
                    new Assert\NotBlank(
                        message: 'Il faut remplir le champ',
                    ),
                    new Assert\Length(
                        min :2,
                        minMessage: 'le libelle doit avoir minium {{limit}} caractères',
                        max :20,
                        maxMessage: 'le libelle doit avoir maximum {{limit}} caractères'
                    ),
                ]
            ])
            ->add('Creer', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Categorie::class,
        ]);
    }
}
