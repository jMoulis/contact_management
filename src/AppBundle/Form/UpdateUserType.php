<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UpdateUserType extends UserType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->remove('plainpassword')
            ->add('email', EmailType::class, [
                'disabled' => $options['is_edit']
            ])
            ->add('username', TextType::class);
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults([
            'is_edit' => true,
        ]);
    }
}
