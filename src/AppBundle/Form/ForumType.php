<?php

namespace Raddit\AppBundle\Form;

use Raddit\AppBundle\Entity\Forum;
use Raddit\AppBundle\Entity\ForumCategory;
use Raddit\AppBundle\Form\Type\MarkdownType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class ForumType extends AbstractType {
    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    public function __construct(AuthorizationCheckerInterface $authorizationChecker) {
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $editing = $builder->getData() && $builder->getData()->getId() !== null;

        $builder
            ->add('name', TextType::class)
            ->add('title', TextType::class)
            ->add('description', MarkdownType::class)
            ->add('category', EntityType::class, [
                'class' => ForumCategory::class,
                'choice_label' => 'name',
                'required' => false,
                'placeholder' => 'forum_form.uncategorized_placeholder',
            ]);

        if ($this->authorizationChecker->isGranted('ROLE_ADMIN')) {
            $builder->add('featured', CheckboxType::class, [
                'required' => false,
            ]);
        }

        $builder->add('submit', SubmitType::class, [
            'label' => $editing ? 'forum_form.save' : 'forum_form.create',
        ]);

        if ($editing && $this->authorizationChecker->isGranted('delete', $builder->getData())) {
            $builder->add('delete', SubmitType::class);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => Forum::class,
            'label_format' => 'forum_form.%name%',
        ]);
    }
}
