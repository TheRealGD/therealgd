<?php

namespace Raddit\AppBundle\Form;

use Raddit\AppBundle\Entity\User;
use Raddit\AppBundle\Form\EventListener\PasswordEncodingSubscriber;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserType extends AbstractType {
    /**
     * @var UserPasswordEncoderInterface
     */
    private $encoder;

    /**
     * @param UserPasswordEncoderInterface $encoder
     */
    public function __construct(UserPasswordEncoderInterface $encoder) {
        $this->encoder = $encoder;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $editing = $builder->getData() && $builder->getData()->getId() !== null;

        $builder
            ->add('username', TextType::class)
            ->add('password', RepeatedType::class, [
                'property_path' => 'plainPassword',
                'required' => !$editing,
                'first_name' => $editing ? 'new_password' : 'password',
                'second_name' => $editing ? 'repeat_new_password' : 'repeat_password',
                'type' => PasswordType::class,
            ])
            ->add('email', EmailType::class);

        // make the submit button always appear at the bottom
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($editing) {
            $event->getForm()->add('submit', SubmitType::class, [
                'label' => 'user_form.'.($editing ? 'save' : 'register'),
            ]);
        }, 20);

        $builder->addEventSubscriber(new PasswordEncodingSubscriber($this->encoder));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => User::class,
            'label_format' => 'user_form.%name%',
            'validation_groups' => function (FormInterface $form) {
                if ($form->getData()->getId() !== null) {
                    return ['Default', 'editing'];
                }

                return ['Default', 'registration'];
            },
        ]);
    }
}
