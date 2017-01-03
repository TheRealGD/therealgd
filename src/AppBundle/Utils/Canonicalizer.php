<?php

namespace Raddit\AppBundle\Utils;

use Symfony\Component\PropertyAccess\PropertyAccess;

final class Canonicalizer {
    /**
     * @param CanonicalizableInterface $entity
     */
    public static function canonicalize(CanonicalizableInterface $entity) {
        $accessor = PropertyAccess::createPropertyAccessor();

        foreach ($entity->getCanonicalizableFields() as $field => $canonicalField) {
            $nonCanonical = $accessor->getValue($entity, $field);

            if ($nonCanonical !== null) {
                $canonical = mb_strtolower($nonCanonical, 'UTF-8');
                $accessor->setValue($entity, $canonicalField, $canonical);
            }
        }
    }
}
