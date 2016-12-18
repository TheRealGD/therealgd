<?php

namespace Raddit\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
 */
class Url extends Submission {
    /**
     * @ORM\Column(type="text")
     *
     * @Assert\Length(max=2000, charset="8bit")
     * @Assert\NotBlank()
     * @Assert\Url(protocols={"http", "https"})
     *
     * @see https://stackoverflow.com/questions/417142/
     *
     * @var string
     */
    private $url;

    /**
     * @return string
     */
    public function getUrl() {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl($url) {
        $this->url = $url;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubmissionType() {
        return 'url';
    }
}
