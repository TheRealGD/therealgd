<?php

namespace Raddit\AppBundle\EventListener;

use Eo\HoneypotBundle\Event\BirdInCageEvent;
use Eo\HoneypotBundle\Events;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class HoneypotListener implements EventSubscriberInterface, LoggerAwareInterface {
    use LoggerAwareTrait;

    public function __construct() {
        $this->setLogger(new NullLogger());
    }

    /**
     * @param BirdInCageEvent $event
     */
    public function onBirdInCage(BirdInCageEvent $event) {
        // TODO: do something more useful with trapped IPs
        $this->logger->notice('Honeypot triggered by IP {ip}', [
            'ip' => $event->getPrey()->getIp(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents() {
        return [
            Events::BIRD_IN_CAGE => 'onBirdInCage',
        ];
    }
}
