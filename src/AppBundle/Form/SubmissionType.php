<?php

namespace Raddit\AppBundle\Form;

use Doctrine\ORM\EntityRepository;
use Raddit\AppBundle\Entity\Forum;
use Raddit\AppBundle\Entity\Submission;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class SubmissionType extends AbstractType {
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $editing = $builder->getData() && $builder->getData()->getId() !== null;

        $builder
            ->add('title', TextareaType::class)
            ->add('url', UrlType::class, ['required' => false])
            ->add('body', TextareaType::class, [
                'required' => false,
                'trim' => false,
            ]);

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
            'label_format' => 'submission_form.%name%',
        ]);
    }
}
