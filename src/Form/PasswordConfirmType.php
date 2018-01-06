<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Generic type for actions that ask for nothing but the password of the current
 * user.
 */
class PasswordConfirmType extends AbstractType {
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('password', PasswordType::class, [
                'label' => 'label.password',
                'constraints' => [
                    new NotBlank(),
                    new UserPassword(),
                ],
            ])
            ->add('confirm', SubmitType::class, [
                'label' => 'label.confirm',
            ]);
    }
}
