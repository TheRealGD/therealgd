<?php

namespace Raddit\AppBundle\Utils;

/**
 * Describes entities that have separate fields for display names and canonical
 * names, e.g. usernames.
 */
interface CanonicalizableInterface {
    /**
     * `['field' => 'canonicalField']`
     *
     * @return array
     */
    public function getCanonicalizableFields();
}
