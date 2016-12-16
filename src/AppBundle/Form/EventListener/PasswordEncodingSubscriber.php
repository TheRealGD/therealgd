<?php

namespace Raddit\AppBundle\Form\EventListener;

use Raddit\AppBundle\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

final class PasswordEncodingSubscriber implements EventSubscriberInterface {
    /**
     * @var UserPasswordEncoderInterface
     */
    private $encoder;

    /**
     * @param UserPasswordEncoderInterface $encoder
     */
    public function __construct(UserPasswordEncoderInterface $encoder) {
        $this->encoder = $encoder;
    }

    public function onPostSubmit(FormEvent $event) {
        if ($event->getForm()->getErrors()->count() > 0) {
            return;
        }

        /** @var User $user */
        $user = $event->getForm()->getData();

        $encoded = $this->encoder->encodePassword($user, $user->getPlainPassword());
        $user->setPassword($encoded);
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents() {
        return [
            FormEvents::POST_SUBMIT => ['onPostSubmit', -200],
        ];
    }
}
