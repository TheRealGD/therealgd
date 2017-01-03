<?php

namespace Raddit\AppBundle\Form\EventListener;

use Raddit\AppBundle\Utils\CanonicalizableInterface;
use Raddit\AppBundle\Utils\Canonicalizer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Form event subscriber that canonicalizes entity fields.
 *
 * Why is there a similar event subscriber both for forms and for Doctrine, you
 * may ask? Simply put, sometimes canonicalization needs to happen before an
 * object is persisted to the database, e.g. when using {@link UniqueValidator}
 * to check for duplicate occurrences of a canonical field. And since not all
 * entities will be created through a form, having only a form listener would be
 * insufficient.
 *
 * @see \Raddit\AppBundle\EventListener\UpdateCanonicalFieldsListener
 */
final class CanonicalizationSubscriber implements EventSubscriberInterface {
    /**
     * @param FormEvent $event
     */
    public function onPostSubmit(FormEvent $event) {
        $data = $event->getData();

        if ($data instanceof CanonicalizableInterface) {
            Canonicalizer::canonicalize($data);
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
