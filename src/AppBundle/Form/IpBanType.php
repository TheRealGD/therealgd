<?php

namespace Raddit\AppBundle\Form;

use Raddit\AppBundle\Form\DataTransformer\UserTransformer;
use Raddit\AppBundle\Form\Model\IpBanData;
use Raddit\AppBundle\Repository\UserRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class IpBanType extends AbstractType {
    /**
     * @var UserRepository
     */
    private $userRepository;

    public function __construct(UserRepository $userRepository) {
        $this->userRepository = $userRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('ip', TextType::class)
            ->add('reason', TextType::class)
            ->add('expiryDate', DateTimeType::class, [
                'date_widget' => 'single_text',
                'label' => 'ban_form.expiry_date',
                'time_widget' => 'single_text',
                'required' => false,
            ])
            ->add('user', TextType::class, [
                'required' => false,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'ban_form.ban',
            ]);

        $builder->get('user')->addModelTransformer(
            new UserTransformer($this->userRepository)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => IpBanData::class,
            'label_format' => 'ban_form.%name%',
        ]);
    }
}
