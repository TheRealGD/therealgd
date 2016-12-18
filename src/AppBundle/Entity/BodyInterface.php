<?php

namespace Raddit\AppBundle\Entity;

/**
 * Interface for entities that have a rendered body and a source body. Useful
 * for event listeners and such.
 *
 * Raw body = source, non-raw body = HTML.
 */
interface BodyInterface {
    /**
     * @return string
     */
    public function getBody();

    /**
     * @param string $body
     */
    public function setBody($body);

    /**
     * @return string
     */
    public function getRawBody();

    /**
     * @param string $rawBody
     */
    public function setRawBody($rawBody);
}
