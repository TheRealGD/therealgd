<?php

namespace Raddit\AppBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Raddit\AppBundle\Utils\CanonicalizableInterface;
use Raddit\AppBundle\Utils\Canonicalizer;

/**
 * Doctrine event subscriber that updates the canonical fields of an entity when
 * working with that entity.
 */
final class UpdateCanonicalFieldsListener implements EventSubscriber {
    /**
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args) {
        $this->canonicalize($args);
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function preUpdate(LifecycleEventArgs $args) {
        $this->canonicalize($args);
    }

    /**
     * @param LifecycleEventArgs $args
     */
    private function canonicalize(LifecycleEventArgs $args) {
        $entity = $args->getEntity();

        if ($entity instanceof CanonicalizableInterface) {
            Canonicalizer::canonicalize($entity);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents() {
        return ['prePersist', 'preUpdate'];
    }
}
