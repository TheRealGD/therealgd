<?php

namespace Raddit\AppBundle\Form;

use Raddit\AppBundle\Entity\MessageThread;
use Raddit\AppBundle\Form\Type\MarkdownType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MessageThreadType extends AbstractType {
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('title', TextareaType::class)
            ->add('body', MarkdownType::class, [
                'label' => 'message_form.message',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'message_form.send',
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => MessageThread::class,
            'label_format' => 'message_form.%name%',
        ]);
    }
}
