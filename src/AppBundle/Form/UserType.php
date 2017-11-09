<?php

namespace AppBundle\Form;

use Eo\HoneypotBundle\Form\Type\HoneypotType;
use Gregwar\CaptchaBundle\Type\CaptchaType;
use AppBundle\Form\EventListener\PasswordEncodingSubscriber;
use AppBundle\Form\Model\UserData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

final class UserType extends AbstractType {
    /**
     * @var UserPasswordEncoderInterface
     */
    private $encoder;

    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * @param UserPasswordEncoderInterface  $encoder
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(
        UserPasswordEncoderInterface $encoder,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->encoder = $encoder;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        if ($options['honeypot']) {
            $builder->add('phone', HoneypotType::class);
        }

        $editing = $builder->getData() && $builder->getData()->getEntityId();

        $builder
            ->add('username', TextType::class)
            ->add('password', RepeatedType::class, [
                'property_path' => 'plainPassword',
                'required' => !$editing,
                'first_options' => ['label' => $editing ? 'user_form.new_password' : 'user_form.password'],
                'second_options' => ['label' => $editing ? 'user_form.repeat_new_password' : 'user_form.repeat_password'],
                'type' => PasswordType::class,
            ])
            ->add('email', EmailType::class, [
                'required' => false,
            ]);

        if (!$editing) {
            $builder->add('verification', CaptchaType::class, [
                'as_url' => true,
                'reload' => true,
            ]);
        }

        $builder->add('submit', SubmitType::class, [
            'label' => 'user_form.'.($editing ? 'save' : 'register'),
        ]);

        $builder->addEventSubscriber(new PasswordEncodingSubscriber($this->encoder));
    }

    public function finishView(FormView $view, FormInterface $form, array $options) {
        if ($form->getData() && $form->getData()->getEntityId()) {
            // don't autocomplete the password fields when editing the user
            $view['password']['first']->vars['attr']['auto-complete'] = 'new-password';
            $view['password']['second']->vars['attr']['auto-complete'] = 'new-password';
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => UserData::class,
            'honeypot' => true,
            'label_format' => 'user_form.%name%',
            'validation_groups' => function (FormInterface $form) {
                if ($form->getData()->getEntityId() !== null) {
                    $groups[] = 'edit';
                } else {
                    $groups[] = 'registration';
                }

                return $groups;
            },
        ]);

        $resolver->setAllowedTypes('honeypot', ['bool']);
    }
}
