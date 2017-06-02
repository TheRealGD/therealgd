<?php

namespace Raddit\AppBundle\Form;

use Raddit\AppBundle\Entity\MessageReply;
use Raddit\AppBundle\Form\Type\MarkdownType;
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
                'label' => 'message_reply_form.message',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'message_reply_form.reply',
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => MessageReply::class,
            'label_format' => 'message_reply_form.%name%',
        ]);
    }
}
