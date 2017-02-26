<?php

namespace Raddit\AppBundle\Form;

use Raddit\AppBundle\Entity\Submission;
use Raddit\AppBundle\Form\EventListener\MarkdownSubscriber;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class SubmissionType extends AbstractType {
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('title', TextareaType::class)
            ->add('url', UrlType::class, ['required' => false])
            ->add('body', TextareaType::class, [
                'property_path' => 'rawBody',
                'required' => false,
            ])
            ->add('submit', SubmitType::class);

        $builder->addEventSubscriber(new MarkdownSubscriber());
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => Submission::class,
            'label_format' => 'submission_form.%name%',
        ]);
    }
}
