<?php

namespace Raddit\AppBundle\Form;

use Raddit\AppBundle\Entity\Ban;
use Raddit\AppBundle\Entity\User;
use Raddit\AppBundle\Repository\UserRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class BanType extends AbstractType {
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
            ->add('expiry_date', DateTimeType::class, [
                'date_widget' => 'single_text',
                'time_widget' => 'single_text',
                'required' => false,
            ])
            ->add('user', TextType::class, [
                'required' => false,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'ban_form.ban',
            ]);

        $builder->get('user')->addModelTransformer(new CallbackTransformer(
            function ($user) {
                return $user instanceof User ? $user->getUsername() : null;
            },
            function ($username) {
                return strlen($username) > 0
                    ? $this->userRepository->loadUserByUsername($username)
                    : null;
            }
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => Ban::class,
            'label_format' => 'ban_form.%name%',
        ]);
    }
}
