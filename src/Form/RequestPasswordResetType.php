<?php

namespace App\Form;

use App\Form\Model\RequestPasswordReset;
use Gregwar\CaptchaBundle\Type\CaptchaType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class RequestPasswordResetType extends AbstractType {
    private $bypass;

    /**
     * @param bool $bypass enable bypass code for unit testing
     */
    public function __construct(bool $bypass) {
        $this->bypass = $bypass;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('email', EmailType::class)
            ->add('verification', CaptchaType::class, [
                'bypass_code' => $this->bypass ? 'bypass' : null,
                'label' => 'label.verification',
                'as_url' => true,
                'reload' => true,
            ])
            ->add('submit', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => RequestPasswordReset::class,
            'label_format' => 'request_password_reset_form.%name%',
        ]);
    }
}
