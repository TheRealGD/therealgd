<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

/**
 * @ORM\Entity(repositoryClass="App\Repository\WikiRevisionRepository")
 * @ORM\Table(name="wiki_revisions")
 */
class WikiRevision {
    /**
     * @ORM\Column(type="uuid")
     * @ORM\Id()
     *
     * @var Uuid
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     *
     * @var string
     */
    private $title;

    /**
     * @ORM\Column(type="text")
     *
     * @var string
     */
    private $body;

    /**
     * @ORM\JoinColumn(nullable=false)
     * @ORM\ManyToOne(targetEntity="WikiPage", inversedBy="revisions")
     *
     * @var WikiPage
     */
    private $page;

    /**
     * @ORM\Column(type="datetimetz")
     *
     * @var \DateTime
     */
    private $timestamp;

    /**
     * @ORM\JoinColumn(nullable=false)
     * @ORM\ManyToOne(targetEntity="User")
     *
     * @var User
     */
    private $user;

    public function __construct(
        WikiPage $page,
        string $title,
        string $body,
        User $user,
        \DateTime $timestamp = null
    ) {
        $this->id = Uuid::uuid4();
        $this->page = $page;
        $this->title = $title;
        $this->body = $body;
        $this->user = $user;
        $this->timestamp = $timestamp ?:
            \DateTime::createFromFormat('U.u', microtime(true));

        $this->page->addRevision($this);
    }

    public function getId(): Uuid {
        return $this->id;
    }

    public function getTitle(): string {
        return $this->title;
    }

    public function getBody(): string {
        return $this->body;
    }

    public function getPage(): WikiPage {
        return $this->page;
    }

    public function getTimestamp(): \DateTime {
        return $this->timestamp;
    }

    public function getUser(): User {
        return $this->user;
    }
}
