<?php

namespace Raddit\AppBundle\Form;

use Raddit\AppBundle\Entity\Forum;
use Raddit\AppBundle\Entity\Url;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class UrlType extends AbstractType {
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('title', TextareaType::class)
            ->add('url', \Symfony\Component\Form\Extension\Core\Type\UrlType::class)
            ->add('submit', SubmitType::class);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => Url::class,
            'label_format' => 'submission_form.%name%',
        ]);
    }
}
