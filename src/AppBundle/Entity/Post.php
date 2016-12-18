<?php

namespace Raddit\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
 */
class Post extends Submission implements BodyInterface {
    /**
     * @ORM\Column(type="text")
     *
     * @var string
     */
    private $body;

    /**
     * @ORM\Column(type="text")
     *
     * @Assert\NotBlank()
     * @Assert\Length(max=25000)
     *
     * @var string
     */
    private $rawBody;

    /**
     * {@inheritdoc}
     */
    public function getBody() {
        return $this->body;
    }

    /**
     * {@inheritdoc}
     */
    public function setBody($body) {
        $this->body = $body;
    }

    /**
     * {@inheritdoc}
     */
    public function getRawBody() {
        return $this->rawBody;
    }

    /**
     * {@inheritdoc}
     */
    public function setRawBody($rawBody) {
        $this->rawBody = $rawBody;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubmissionType() {
        return 'post';
    }
}
