<?php

namespace App\Form\Model;

use App\Entity\User;
use App\Entity\WikiPage;
use App\Entity\WikiRevision;
use Symfony\Component\Validator\Constraints as Assert;

class WikiData {
    /**
     * @Assert\NotBlank()
     * @Assert\Length(min=1, max=80)
     *
     * @var string|null
     */
    private $title;

    /**
     * @Assert\NotBlank()
     * @Assert\Length(min=1, max=250000)
     *
     * @var string|null
     */
    private $body;

    public static function createFromPage(WikiPage $page) {
        $self = new self();

        $self->title = $page->getLatestRevision()->getTitle();
        $self->body = $page->getLatestRevision()->getBody();

        return $self;
    }

    public function toPage(string $path, User $user): WikiPage {
        return new WikiPage($path, $this->title, $this->body, $user);
    }

    public function updatePage(WikiPage $page, User $user) {
        $revision = new WikiRevision($page, $this->title, $this->body, $user);

        $page->addRevision($revision);
    }

    public function getTitle() {
        return $this->title;
    }

    public function setTitle($title) {
        $this->title = $title;
    }

    public function getBody() {
        return $this->body;
    }

    public function setBody($body) {
        $this->body = $body;
    }
}
