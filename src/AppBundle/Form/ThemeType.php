<?php

namespace Raddit\AppBundle\Form;

use Doctrine\ORM\EntityManagerInterface;
use Raddit\AppBundle\Entity\ThemeRevision;
use Raddit\AppBundle\Form\Model\ThemeData;
use Raddit\AppBundle\Repository\ThemeRepository;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ThemeType extends AbstractType {
    /**
     * @var ThemeRepository
     */
    private $themeRepository;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(
        ThemeRepository $themeRepository,
        EntityManagerInterface $em
    ) {
        $this->themeRepository = $themeRepository;
        $this->em = $em;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('parent', TextType::class, [
                'invalid_message' => 'No such theme.',
                'label' => 'label.parent_theme',
                'required' => false,
            ])
            ->add('name', TextType::class, [
                'label' => 'label.name',
            ])
            ->add('commonCss', TextareaType::class, [
                'label' => 'label.common_css',
                'required' => false,
            ])
            ->add('dayCss', TextareaType::class, [
                'label' => 'label.day_css',
                'required' => false,
            ])
            ->add('nightCss', TextareaType::class, [
                'label' => 'label.night_css',
                'required' => false,
            ])
            ->add('appendToDefaultStyle', CheckboxType::class, [
                'required' => false,
                'label' => 'label.append_to_default_style',
            ])
            ->add('comment', TextareaType::class, [
                'label' => 'label.comment',
                'required' => false,
            ]);

        $builder->get('parent')->addModelTransformer(new CallbackTransformer(
            function ($value) {
                if ($value instanceof ThemeRevision) {
                    return $value->getId()->toString();
                }

                return '';
            },
            function ($value) {
                $value = trim($value);

                if ($value === '') {
                    return null;
                }

                if (preg_match('!^(\w{3,25})\s*/\s*(.+)$!', $value, $matches)) {
                    list (, $username, $name) = $matches;

                    $theme = $this->themeRepository->findOneByUsernameAndName($username, $name);

                    $revision = $theme ? $theme->getLatestRevision() : null;
                } elseif (Uuid::isValid(trim($value))) {
                    $revision = $this->em->find(ThemeRevision::class, $value);
                }

                if (empty($revision)) {
                    throw new TransformationFailedException();
                }

                return $revision;
            }
        ));
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => ThemeData::class,
        ]);
    }
}
