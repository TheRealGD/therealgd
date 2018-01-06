<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class MarkdownType extends AbstractType {
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'trim' => false,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent() {
        return TextareaType::class;
    }
}
