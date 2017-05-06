<?php

namespace Raddit\AppBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Raddit\AppBundle\Entity\Comment;
use Raddit\AppBundle\Entity\Submission;
use Raddit\AppBundle\Entity\Vote;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Attaches the current IP address to entities that have IP address fields.
 */
final class AttachIpToEntityListener {
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @param RequestStack $requestStack
     */
    public function __construct(RequestStack $requestStack) {
        $this->requestStack = $requestStack;
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args) {
        $entity = $args->getEntity();

        if ((
            $entity instanceof Vote ||
            $entity instanceof Submission ||
            $entity instanceof Comment
        ) && $entity->getIp() === null) {
            $ip = $this->requestStack->getCurrentRequest()->getClientIp();

            $entity->setIp($ip);
        }
    }
}
