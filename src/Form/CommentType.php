<?php

namespace App\Form;

use App\Entity\Forum;
use App\Form\Model\CommentData;
use App\Form\Type\HoneypotType;
use App\Form\Type\MarkdownType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class CommentType extends AbstractType {
    use UserFlagTrait;

    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        TokenStorageInterface $tokenStorage
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        if ($options['honeypot']) {
            $builder->add('email', HoneypotType::class);
        }

        $builder->add('comment', MarkdownType::class, [
            'property_path' => 'body',
        ]);

        $this->addUserFlagOption($builder, $options['forum']);

        $builder->add('submit', SubmitType::class);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => CommentData::class,
            'forum' => null, // for UserFlagTrait
            'honeypot' => true,
            'label_format' => 'comment_form.%name%',
            'validation_groups' => function (FormInterface $form) {
                $groups = ['Default'];
                $trusted = $this->authorizationChecker->isGranted('ROLE_TRUSTED_USER');

                /* @noinspection PhpUndefinedMethodInspection */
                if ($form->getData() && $form->getData()->getBody()) {
                    $groups[] = 'editcomment';

                    if (!$trusted) {
                        $groups[] = 'untrusted_user_editcomment';
                    }
                } else {
                    $groups[] = 'comment';

                    if (!$trusted) {
                        $groups[] = 'untrusted_user_comment';
                    }
                }

                return $groups;
            },
        ]);

        $resolver->setAllowedTypes('forum', ['null', Forum::class]); // ditto
        $resolver->setAllowedTypes('honeypot', ['bool']);
    }
}
