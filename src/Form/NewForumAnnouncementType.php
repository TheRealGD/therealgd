<?php

namespace App\Form;

use App\Form\Model\NewForumAnnouncementData;
use Symfony\Component\Form\AbstractType;
use App\Form\Type\MarkdownType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NewForumAnnouncementType extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('announcement', TextType::class, [
                'label' => 'front_page_configuration_form.announcement',
                'required' => true
            ])
            ->add('threadTitle', TextType::class, [
                'label' => 'front_page_configuration_form.thread_title',
                'required' => true
            ])
            ->add('threadContent', MarkdownType::class, [
                'label' => 'front_page_configuration_form.thread_content',
                'required' => true
            ])
            ->add('id', HiddenType::class, [])
            ->add('forumId', HiddenType::class, [])
            ->add('submit', SubmitType::class, [
                'label' => 'front_page_configuration_form.save',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => NewForumAnnouncementData::class,
        ]);
    }
}
