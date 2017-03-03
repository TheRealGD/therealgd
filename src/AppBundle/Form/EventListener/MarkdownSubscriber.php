<?php

namespace Raddit\AppBundle\Form\EventListener;

use Raddit\AppBundle\Entity\BodyInterface;
use Raddit\AppBundle\Utils\MarkdownConverter;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

final class MarkdownSubscriber implements EventSubscriberInterface {
    /**
     * @param FormEvent $event
     */
    public function onPostSubmit(FormEvent $event) {
        if ($event->getForm()->getErrors()->count() > 0) {
            return;
        }

        $entity = $event->getForm()->getData();

        if (!$entity instanceof BodyInterface) {
            return;
        }

        if (strlen(trim($entity->getRawBody())) > 0) {
            $html = MarkdownConverter::convert($entity->getRawBody());

            $entity->setBody($html);
        } else {
            $entity->setBody(null);
        }
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
