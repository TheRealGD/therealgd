<?php

namespace Raddit\AppBundle\Form;

use Raddit\AppBundle\Entity\Stylesheet;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StylesheetType extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $editing = $builder->getData() && $builder->getData()->getId();

        $builder
            ->add('name', TextType::class, [
                'label' => 'stylesheets.name',
            ])
            ->add('css', TextareaType::class, [
                'label' => 'stylesheets.css',
            ])
            ->add('appendToDefaultStyle', CheckboxType::class, [
                'required' => false,
                'label' => 'stylesheets.append_to_default_style',
            ])
            ->add('nightFriendly', CheckboxType::class, [
                'required' => false,
                'label' => 'stylesheets.night_friendly',
            ])
            ->add('submit', SubmitType::class, [
                'label' => $editing ? 'stylesheets.edit_stylesheet' : 'stylesheets.create_stylesheet',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => Stylesheet::class,
        ]);
    }
}
