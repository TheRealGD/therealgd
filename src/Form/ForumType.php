<?php

namespace App\Form;

use App\Entity\ForumCategory;
use App\Form\Model\ForumData;
use App\Form\Type\HoneypotType;
use App\Form\Type\MarkdownType;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
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
        if ($options['honeypot']) {
            $builder->add('email', HoneypotType::class);
        }

        /* @var ForumData $data */
        $data = $builder->getData();
        $editing = $data && $data->getEntityId();

        $builder
            ->add('name', TextType::class)
            ->add('title', TextType::class)
            ->add('description', TextareaType::class)
            ->add('sidebar', MarkdownType::class, [
                'label' => 'label.sidebar',
            ])
            ->add('category', EntityType::class, [
                'class' => ForumCategory::class,
                'choice_label' => 'name',
                'query_builder' => function (EntityRepository $repository) {
                    return $repository->createQueryBuilder('fc')
                        ->orderBy('fc.name', 'ASC');
                },
                'required' => false,
                'placeholder' => 'forum_form.uncategorized_placeholder',
            ])
        ;

        if ($this->authorizationChecker->isGranted('ROLE_ADMIN')) {
            $builder->add('featured', CheckboxType::class, [
                'required' => false,
            ]);
        }

        $builder->add('submit', SubmitType::class, [
            'label' => $editing ? 'forum_form.save' : 'forum_form.create',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => ForumData::class,
            'label_format' => 'forum_form.%name%',
            'honeypot' => true,
            'validation_groups' => function (FormInterface $form) {
                $editing = $form->getData() && $form->getData()->getEntityId();

                return $editing ? ['edit'] : ['create'];
            },
        ]);

        $resolver->setAllowedTypes('honeypot', ['bool']);
    }
}
