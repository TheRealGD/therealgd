<?php

namespace Raddit\AppBundle\Form;

use Raddit\AppBundle\Form\Model\ThemeData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ThemeType extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('name', TextType::class, [
                'label' => 'label.name',
            ])
            ->add('commonCss', TextareaType::class, [
                'label' => 'label.common_css',
                'required' => false,
            ])
            ->add('dayCss', TextareaType::class, [
                'label' => 'label.day_css',
                'required' => false,
            ])
            ->add('nightCss', TextareaType::class, [
                'label' => 'label.night_css',
                'required' => false,
            ])
            ->add('appendToDefaultStyle', CheckboxType::class, [
                'required' => false,
                'label' => 'label.append_to_default_style',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => ThemeData::class,
        ]);
    }
}
