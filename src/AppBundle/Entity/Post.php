<?php

namespace Raddit\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class Post extends Submission {
    /**
     * @ORM\Column(type="text")
     *
     * @var string
     */
    private $source;

    /**
     * @ORM\Column(type="text")
     *
     * @var string
     */
    private $rendered;

    /**
     * @return string
     */
    public function getSource() {
        return $this->source;
    }

    /**
     * @param string $source
     */
    public function setSource($source) {
        $this->source = $source;
    }

    /**
     * @return string
     */
    public function getRendered() {
        return $this->rendered;
    }

    /**
     * @param string $rendered
     */
    public function setRendered($rendered) {
        $this->rendered = $rendered;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubmissionType() {
        return 'post';
    }
}
