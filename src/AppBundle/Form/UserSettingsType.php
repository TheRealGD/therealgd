<?php

namespace Raddit\AppBundle\Form;

use Raddit\AppBundle\Entity\Theme;
use Raddit\AppBundle\Entity\User;
use Raddit\AppBundle\Form\Model\UserData;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class UserSettingsType extends AbstractType {
    private $localeChoices = [];

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('locale', ChoiceType::class, [
                'choices' => $this->getLocaleChoices(),
                'choice_translation_domain' => false,
            ])
            ->add('front_page', ChoiceType::class, [
                'choices' => [
                    'label.default' => User::FRONT_DEFAULT,
                    'label.featured' => User::FRONT_FEATURED,
                    'label.subscribed' => User::FRONT_SUBSCRIBED,
                    'label.all' => User::FRONT_ALL,
                    'label.moderated' => User::FRONT_MODERATED,
                ],
                'label' => 'label.front_page',
            ])
            ->add('night_mode', CheckboxType::class, [
                'required' => false,
            ])
            ->add('show_custom_stylesheets', CheckboxType::class, [
                'required' => false,
            ])
            ->add('preferred_theme', EntityType::class, [
                'class' => Theme::class,
                'choice_label' => 'name',
                'group_by' => 'author.username',
                'label' => 'label.preferred_theme',
                'placeholder' => 'placeholder.default',
                'required' => false,
            ])
            ->add('openExternalLinksInNewTab', CheckboxType::class, [
                'required' => false,
                'label' => 'label.open_external_links_in_new_tab',
            ])
            ->add('save', SubmitType::class);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => UserData::class,
            'label_format' => 'user_settings_form.%name%',
            'validation_groups' => ['settings'],
        ]);
    }

    public function getLocaleChoices(): array {
        return $this->localeChoices;
    }

    public function setLocaleChoices(array $localeChoices) {
        $this->localeChoices = $localeChoices;
    }
}
