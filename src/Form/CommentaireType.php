<?php

namespace App\Form;


use App\Entity\Commentaire;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class CommentaireType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('contenu', TextareaType::class,[
                'constraints'=>[
                    new Assert\NotBlank(
                        message: 'Il faut remplir le champ',
                    ),
                    new Assert\Length(
                        min :2,
                        minMessage: 'le contenu doit avoir minium {{limit}} caractères',
                        max :1000,
                        maxMessage: 'le contenu doit avoir maximum {{limit}} caractères'
                    ),
                ]
            ])
            ->add('Ajouter', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Commentaire::class,
        ]);
    }
}
