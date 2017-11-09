<?php

namespace AppBundle\Form;

use AppBundle\Entity\Theme;
use AppBundle\Form\Model\ForumData;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
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
            ->add('theme', EntityType::class, [
                'class' => Theme::class,
                'choice_label' => 'name',
                'group_by' => 'author.username',
                'label' => 'label.theme',
                'placeholder' => 'placeholder.default',
                'property_path' => 'theme',
                'required' => false,
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
