<?php

namespace Raddit\AppBundle\Form\Model;

use Raddit\AppBundle\Entity\WikiPage;
use Symfony\Component\Validator\Constraints as Assert;

class Wiki {
    /**
     * @Assert\NotBlank()
     * @Assert\Length(min=1, max=80)
     *
     * @var string|null
     */
    public $title;

    /**
     * @Assert\NotBlank()
     * @Assert\Length(min=1, max=10000)
     *
     * @var string|null
     */
    public $body;

    public static function createFromPage(WikiPage $page) {
        $self = new self();

        $self->title = $page->getCurrentRevision()->getTitle();
        $self->body = $page->getCurrentRevision()->getBody();

        return $self;
    }
}
