<?php

namespace App\Form\Type;

use App\Entity\Theme;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ThemeSelectorType extends AbstractType {
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'class' => Theme::class,
            'choice_label' => 'name',
            'group_by' => 'author.username',
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('t')->join('t.revisions', 'tr');
            },
            'placeholder' => 'placeholder.default',
            'required' => false,
        ]);
    }

    public function getParent() {
        return EntityType::class;
    }
}
