<?php

namespace Raddit\AppBundle\Form;

use Raddit\AppBundle\Form\Model\UserBanData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Expression;

class BanUserType extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('reason', TextareaType::class, [
                'label' => 'label.reason',
            ])
            ->add('expiresAt', DateTimeType::class, [
                'date_widget' => 'single_text',
                'label' => 'label.expires',
                'required' => false,
                'time_widget' => 'single_text',
            ])
            ->add('ban_ip', CheckboxType::class, [
                'label' => 'label.ban_ip_address',
                'mapped' => false,
                'required' => false,
                'constraints' => new Expression([
                    'expression' => 'this.ban_ip === false || '
                ]),
            ])
            ->add('ip', TextType::class, [
                'label' => 'label.ip_address',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => UserBanData::class,
            'validation_groups' => function (FormInterface $form) {
                $groups = ['ban_user'];

                if ($form->get('ban_ip')->getData()) {
                    $groups[] = 'ban_ip';
                }

                return $groups;
            }
        ]);
    }
}
