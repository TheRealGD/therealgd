<?php

namespace App\Form;

use App\Entity\ForumWebhook;
use App\Form\Model\ForumWebhookData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ForumWebhookType extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $editing = $builder->getData() && $builder->getData()->getEntityId();

        $builder
            ->add('event', ChoiceType::class, [
                'choices' => ForumWebhook::EVENTS,
                'choice_label' => function ($key) {
                    return 'label.event.'.$key;
                },
                'label' => 'label.event',
                'placeholder' => $editing ? null : 'placeholder.choose_one',
            ])
            ->add('url', UrlType::class, [
                'label' => 'label.url',
            ])
            ->add('secretToken', TextareaType::class, [
                'label' => 'label.secret_token',
                'required' => false,
            ])
            ->add('submit', SubmitType::class, [
                'label' => $editing ? 'action.save' : 'action.add',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => ForumWebhookData::class,
        ]);
    }
}
