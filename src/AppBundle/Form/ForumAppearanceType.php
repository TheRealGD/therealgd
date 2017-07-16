<?php

namespace Raddit\AppBundle\Form;

use Doctrine\ORM\EntityRepository;
use Raddit\AppBundle\Entity\Forum;
use Raddit\AppBundle\Entity\Stylesheet;
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
                'class' => Stylesheet::class,
                'choice_label' => 'name',
                'label' => 'label.theme',
                'placeholder' => 'placeholder.default',
                'property_path' => 'stylesheet',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('s')->where('s.nightFriendly = FALSE');
                },
                'required' => false,
            ])
            ->add('nightTheme', EntityType::class, [
                'class' => Stylesheet::class,
                'choice_label' => 'name',
                'label' => 'label.night_theme',
                'placeholder' => 'placeholder.default',
                'property_path' => 'nightStylesheet',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('s')->where('s.nightFriendly = TRUE');
                },
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
            'data_class' => Forum::class,
        ]);
    }
}
