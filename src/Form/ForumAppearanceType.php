<?php

namespace App\Form;

use App\Form\Model\ForumData;
use App\Form\Type\ThemeSelectorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ForumAppearanceType extends AbstractType {
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('theme', ThemeSelectorType::class, [
                'label' => 'label.theme',
            ])
            ->add('save', SubmitType::class, [
                'label' => 'label.save_settings',
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => ForumData::class,
            'validation_groups' => ['appearance'],
        ]);
    }
}
