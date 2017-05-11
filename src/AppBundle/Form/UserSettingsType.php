<?php

namespace Raddit\AppBundle\Form;

use Raddit\AppBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Intl\Intl;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class UserSettingsType extends AbstractType {
    const LOCALES = ['en', 'es', 'nb'];

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('locale', ChoiceType::class, [
                'choices' => $this->getLocaleChoices(),
                'choice_translation_domain' => false,
            ])
            ->add('save', SubmitType::class)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => User::class,
            'label_format' => 'user_settings_form.%name%',
        ]);
    }

    private function getLocaleChoices(): array {
        $localeBundle = Intl::getLocaleBundle();
        $choices = [];

        foreach (self::LOCALES as $locale) {
            $choices[$localeBundle->getLocaleName($locale, $locale)] = $locale;
        }

        return $choices;
    }
}
