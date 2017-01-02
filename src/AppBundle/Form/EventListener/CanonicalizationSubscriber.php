<?php

namespace Raddit\AppBundle\Form\EventListener;

use Raddit\AppBundle\Utils\CanonicalizableInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\PropertyAccess\PropertyAccess;

final class CanonicalizationSubscriber implements EventSubscriberInterface {
    /**
     * @param FormEvent $event
     */
    public function onPostSubmit(FormEvent $event) {
        $data = $event->getData();

        if (!$data instanceof CanonicalizableInterface) {
            return;
        }

        $accessor = PropertyAccess::createPropertyAccessor();

        foreach ($data->getCanonicalizableFields() as $field => $canonicalField) {
            $nonCanonical = $accessor->getValue($data, $field);

            if ($nonCanonical !== null) {
                $canonical = mb_strtolower($nonCanonical, 'UTF-8');
                $accessor->setValue($data, $canonicalField, $canonical);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents() {
        return [
            // this must run before the validator
            FormEvents::POST_SUBMIT => ['onPostSubmit', 50],
        ];
    }
}
