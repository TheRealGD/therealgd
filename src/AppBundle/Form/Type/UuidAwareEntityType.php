<?php

namespace Raddit\AppBundle\Form\Type;

use Doctrine\Common\Persistence\ObjectManager;
use Raddit\AppBundle\Form\ChoiceList\UuidAwareORMQueryBuilderLoader;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

/**
 * @todo remove this hack
 */
class UuidAwareEntityType extends EntityType {
    /**
     * {@inheritdoc}
     */
    public function getLoader(ObjectManager $manager, $queryBuilder, $class) {
        return new UuidAwareORMQueryBuilderLoader($queryBuilder);
    }
}
