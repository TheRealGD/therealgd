<?php

namespace App\Form;

use App\Form\Model\MessageData;
use App\Form\Type\MarkdownType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MessageReplyType extends AbstractType {
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('body', MarkdownType::class, [
                'label' => 'message_form.message',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'message_form.reply',
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => MessageData::class,
            'label_format' => 'message_form.%name%',
            'validation_groups' => ['reply'],
        ]);
    }
}
