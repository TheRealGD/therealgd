<?php

namespace Raddit\AppBundle\Form;

use Raddit\AppBundle\Entity\Theme;
use Raddit\AppBundle\Form\Model\UserSettings;
use Raddit\AppBundle\Form\Type\UuidAwareEntityType;
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
            ->add('night_mode', CheckboxType::class, [
                'required' => false,
            ])
            ->add('show_custom_stylesheets', CheckboxType::class, [
                'required' => false,
            ])
            ->add('preferred_theme', UuidAwareEntityType::class, [
                'class' => Theme::class,
                'choice_label' => 'name',
                'group_by' => 'author.username',
                'label' => 'label.preferred_theme',
                'placeholder' => 'placeholder.default',
                'required' => false,
            ])
            ->add('save', SubmitType::class);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => UserSettings::class,
            'label_format' => 'user_settings_form.%name%',
        ]);
    }

    public function getLocaleChoices(): array {
        return $this->localeChoices;
    }

    public function setLocaleChoices(array $localeChoices) {
        $this->localeChoices = $localeChoices;
    }
}
