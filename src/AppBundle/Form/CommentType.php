<?php

namespace Raddit\AppBundle\Form;

use Raddit\AppBundle\Entity\Comment;
use Raddit\AppBundle\Form\EventListener\MarkdownSubscriber;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class CommentType extends AbstractType {
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('comment', TextareaType::class, [
                'property_path' => 'rawBody',
            ])
            ->add('submit', SubmitType::class);

        $builder->addEventSubscriber(new MarkdownSubscriber());
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => Comment::class,
            'label_format' => 'comment_form.%name%',
        ]);
    }
}
