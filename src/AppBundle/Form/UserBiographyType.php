<?php

namespace Raddit\AppBundle\Form;

use Raddit\AppBundle\Form\Model\UserData;
use Raddit\AppBundle\Form\Type\MarkdownType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserBiographyType extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('biography', MarkdownType::class, [
                'label' => 'label.biography',
            ])
            ->add('save', SubmitType::class, [
                'label' => 'label.save_settings',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => UserData::class,
        ]);
    }
}
