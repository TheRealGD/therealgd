<?php

namespace App\Form;

use App\Form\Model\RateLimitData;
use App\Entity\UserGroup;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class RateLimitType extends AbstractType {
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

        $this->group = $options['group'];
        $this->rates = $options['rates'];

        $builder
            ->add('rate', IntegerType::class)
            ->add('group', EntityType::class, [
                'class' => UserGroup::class,
                'choice_label' => 'name',
                'query_builder' => function (EntityRepository $repository) {
                    $qb = $repository->createQueryBuilder('ug')
                        ->orderBy('ug.name', 'ASC');
                    if (!is_null($this->group)) {
                        $qb->where('ug.name = :name')
                           ->setParameter('name', $this->group->getName());
                    } else if (count($this->rates) > 0) {
                        $qb->where('ug not in (:rates)')->setParameter('rates', $this->rates);
                    }
                    return $qb;
                },
                'required' => true,
            ]);

    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'group' => null,
            'rates' => [],
            'data_class' => RateLimitData::class,
            'label_format' => 'rate_limit_form.%name%',
            'validation_groups' => function (FormInterface $form) {
                $editing = $form->getData() && $form->getData()->getEntityId();

                return $editing ? ['edit'] : ['create'];
            },
        ]);
    }
}
