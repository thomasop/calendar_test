<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('prenom', TextType::class, [
                'label' => 'Prénom : ',
            ])
            ->add('nom', TextType::class, [
                'label' => 'Nom : ',
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email : ',
            ])
            ->add('sujet', TextType::class, [
                'label' => 'Sujet : ',
            ])
            ->add('content', TextareaType::class, [
                'label' => 'Message : ',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
