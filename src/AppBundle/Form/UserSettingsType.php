<?php

namespace Raddit\AppBundle\Form;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Intl\Intl;

class UserSettingsType extends UserType {
    public function buildForm(FormBuilderInterface $builder, array $options) {
        parent::buildForm($builder, $options);

        $localeBundle = Intl::getLocaleBundle();

        $builder->add('locale', ChoiceType::class, [
            // TODO
            'choices' => [
                $localeBundle->getLocaleName('en', 'en') => 'en',
                $localeBundle->getLocaleName('no', 'nb_NO') => 'no',
            ],
            'choice_translation_domain' => false,
            'required' => false,
        ]);
    }
}
