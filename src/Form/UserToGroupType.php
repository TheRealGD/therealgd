<?php

namespace App\Form;

use App\Form\Model\UserData;
use App\Entity\UserGroup;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class UserToGroupType extends AbstractType {
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

        /* @var ForumData $data */
        $data = $builder->getData();
        $editing = $data && $data->getEntityId();

        $builder->add('group', EntityType::class, [
                'class' => UserGroup::class,
                'choice_label' => 'name',
                'query_builder' => function (EntityRepository $repository) {
                    return $repository->createQueryBuilder('ug')
                        ->orderBy('ug.name', 'ASC');
                },
                'required' => false,
                'placeholder' => 'forum_form.uncategorized_placeholder',
            ]);

        $builder->add('submit', SubmitType::class, [
            'label' => $editing ? 'forum_form.save' : 'forum_form.create',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => UserData::class,
            'label_format' => 'user_form.%name%',
            'validation_groups' => function (FormInterface $form) {
                $editing = $form->getData() && $form->getData()->getEntityId();

                return $editing ? ['edit'] : ['create'];
            },
        ]);
    }
}
