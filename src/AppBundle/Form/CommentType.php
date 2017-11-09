<?php

namespace AppBundle\Form;

use Eo\HoneypotBundle\Form\Type\HoneypotType;
use AppBundle\Entity\Forum;
use AppBundle\Form\Model\CommentData;
use AppBundle\Form\Type\MarkdownType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
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
        ]);

        $resolver->setAllowedTypes('forum', ['null', Forum::class]); // ditto
        $resolver->setAllowedTypes('honeypot', ['bool']);
    }
}
