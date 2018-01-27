<?php

namespace App\Form;

use App\Form\Model\UserFilterData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserFilterType extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('orderBy', ChoiceType::class, [
                'label' => 'label.order_by',
                'choices' => [
                    'label.registration_date' => UserFilterData::ORDER_CREATED,
                    'label.username' => UserFilterData::ORDER_USERNAME,
                ],
            ])
            ->add('role', ChoiceType::class, [
                'label' => 'label.role',
                'choices' => [
                    'label.any' => UserFilterData::ROLE_ANY,
                    'label.admin' => UserFilterData::ROLE_ADMIN,
                    'label.trusted' => UserFilterData::ROLE_TRUSTED,
                    'label.none' => UserFilterData::ROLE_NONE,
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'data_class' => UserFilterData::class,
            'method' => 'GET',
        ]);
    }
}
