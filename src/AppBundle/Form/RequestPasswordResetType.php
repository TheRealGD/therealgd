<?php

namespace Raddit\AppBundle\Form;

use Raddit\AppBundle\Form\Model\RequestPasswordReset;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class RequestPasswordResetType extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('email', EmailType::class)
            ->add('submit', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => RequestPasswordReset::class,
            'label_format' => 'request_password_reset_form.%name%',
        ]);
    }
}
