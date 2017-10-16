<?php

namespace Raddit\AppBundle\Form;

use Doctrine\ORM\EntityRepository;
use Eo\HoneypotBundle\Form\Type\HoneypotType;
use Raddit\AppBundle\Entity\Forum;
use Raddit\AppBundle\Form\Model\SubmissionData;
use Raddit\AppBundle\Form\Type\MarkdownType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class SubmissionType extends AbstractType {
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

        /** @var SubmissionData $data */
        $data = $builder->getData();
        $editing = $data->getEntityId() !== null;
        $forum = $data->getForum();

        $builder
            ->add('title', TextareaType::class)
            ->add('url', UrlType::class, ['required' => false])
            ->add('body', MarkdownType::class, [
                'required' => false,
            ]);

        $builder->add('forum', EntityType::class, [
            'class' => Forum::class,
            // Don't allow choosing other forums when one is already chosen.
            // This prevents security issues when form fields should be present
            // on one forum's submission page, but not another.
            'disabled' => $editing || $forum,
            'choice_label' => 'name',
            'query_builder' => function (EntityRepository $repository) {
                return $repository->createQueryBuilder('f')
                    ->orderBy('f.name', 'ASC');
            },
            'placeholder' => 'placeholder.choose_one',
            'required' => false, // enable a blank choice
        ]);

        if (
            $this->authorizationChecker->isGranted('moderator', $forum) ||
            $this->authorizationChecker->isGranted('ROLE_ADMIN')
        ) {
            $builder->add('sticky', CheckboxType::class, ['required' => false]);
        }

        $this->addUserFlagOption($builder, $forum);

        $builder->add('submit', SubmitType::class, [
            'label' => 'submission_form.'.($editing ? 'edit' : 'create'),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => SubmissionData::class,
            'label_format' => 'submission_form.%name%',
            'honeypot' => true,
            'validation_groups' => function (FormInterface $form) {
                $groups = ['Default'];
                $trusted = $this->authorizationChecker->isGranted('ROLE_TRUSTED_USER');

                /* @noinspection PhpUndefinedMethodInspection */
                if ($form->getData() && $form->getData()->getEntityId()) {
                    $groups[] = 'edit';

                    if (!$trusted) {
                        $groups[] = 'untrusted_user_edit';
                    }
                } else {
                    $groups[] = 'create';

                    if (!$trusted) {
                        $groups[] = 'untrusted_user_create';
                    }
                }

                return $groups;
            },
        ]);

        $resolver->setAllowedTypes('honeypot', ['bool']);
    }
}
