<?php

namespace App\Form;

use App\Entity\Forum;
use App\Form\Model\SubmissionData;
use App\Form\Type\HoneypotType;
use App\Form\Type\MarkdownType;
use Doctrine\ORM\EntityRepository;
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

        if (!$editing) {
            $builder->add('forum', EntityType::class, [
                'class' => Forum::class,
                'choice_label' => 'name',
                'query_builder' => function (EntityRepository $repository) {
                    return $repository->createQueryBuilder('f')
                        ->orderBy('f.name', 'ASC');
                },
                'placeholder' => 'placeholder.choose_one',
                'required' => false, // enable a blank choice
            ]);
        }

        if (
            $editing && $this->authorizationChecker->isGranted('moderator', $forum) ||
            $this->authorizationChecker->isGranted('ROLE_ADMIN')
        ) {
            $builder->add('sticky', CheckboxType::class, ['required' => false]);

            $this->addUserFlagOption($builder, $forum);
        }

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
