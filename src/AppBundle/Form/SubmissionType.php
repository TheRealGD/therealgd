<?php

namespace Raddit\AppBundle\Form;

use Doctrine\ORM\EntityRepository;
use Raddit\AppBundle\Entity\Forum;
use Raddit\AppBundle\Entity\Submission;
use Raddit\AppBundle\Form\Type\MarkdownType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
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
        /** @var Submission $submission */
        $submission = $builder->getData();

        $editing = $submission && $submission->getId() !== null;

        $builder
            ->add('title', TextareaType::class)
            ->add('url', UrlType::class, ['required' => false])
            ->add('body', MarkdownType::class, [
                'required' => false,
            ]);

        $this->addUserFlagOption($builder, $options);

        if (!$editing) {
            $builder->add('forum', EntityType::class, [
                'class' => Forum::class,
                'choice_label' => 'name',
                'query_builder' => function (EntityRepository $repository) {
                    return $repository->createQueryBuilder('f')
                        ->orderBy('f.name', 'ASC');
                },
                'required' => false, // enable a blank choice
            ]);
        }

        $forum = $submission ? $submission->getForum() : null;

        if ($forum && $this->authorizationChecker->isGranted('sticky', $submission)) {
            $builder->add('sticky', CheckboxType::class, ['required' => false]);
        }

        $builder->add('submit', SubmitType::class, [
            'label' => 'submission_form.'.($editing ? 'edit' : 'create'),
        ]);

        if ($editing) {
            $builder->add('delete', SubmitType::class);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => Submission::class,
            'forum' => null,
            'label_format' => 'submission_form.%name%',
        ]);

        $resolver->setAllowedTypes('forum', [Forum::class, 'null']);
    }
}
