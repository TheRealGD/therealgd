<?php

namespace App\Form;

use App\Form\Model\ForumBanData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ForumBanType extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('reason', TextareaType::class, [
            'label' => 'label.reason',
        ]);

        if ($options['intent'] === 'ban') {
            $builder->add('expiryTime', DateTimeType::class, [
                'label' => 'label.expires',
                'date_widget' => 'single_text',
                'time_widget' => 'single_text',
                'required' => false,
            ]);
        }

        $builder->add('ban', SubmitType::class, [
            'label' => $options['intent'] === 'ban' ? 'action.ban' : 'action.unban',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => ForumBanData::class,
            'intent' => null,
            'validation_groups' => function (FormInterface $form) {
                return [$form->getConfig()->getOption('intent')];
            },
        ]);

        $resolver->setRequired('intent');
        $resolver->setAllowedValues('intent', ['ban', 'unban']);
    }
}
