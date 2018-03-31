<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

/**
 * @ORM\Entity()
 * @ORM\Table(name="forum_webhooks")
 */
class ForumWebhook {
    public const EVENT_NEW_SUBMISSION = 'new_submission';
    public const EVENT_EDIT_SUBMISSION = 'edit_submission';
    public const EVENT_NEW_COMMENT = 'new_comment';
    public const EVENT_EDIT_COMMENT = 'edit_comment';

    public const EVENTS = [
        self::EVENT_NEW_SUBMISSION,
        self::EVENT_EDIT_SUBMISSION,
        self::EVENT_NEW_COMMENT,
        self::EVENT_EDIT_COMMENT,
    ];

    /**
     * @ORM\Column(type="uuid")
     * @ORM\Id()
     *
     * @var Uuid
     */
    private $id;

    /**
     * @ORM\JoinColumn(nullable=false)
     * @ORM\ManyToOne(targetEntity="Forum", inversedBy="webhooks")
     *
     * @var Forum
     */
    private $forum;

    /**
     * @ORM\Column(type="text")
     *
     * @var string
     */
    private $event;

    /**
     * @ORM\Column(type="text")
     *
     * @var string
     */
    private $url;

    /**
     * @ORM\Column(type="text", nullable=true)
     *
     * @var string|null
     */
    private $secretToken;

    public function __construct(Forum $forum, string $event, string $url, ?string $secretToken) {
        $this->id = Uuid::uuid4();
        $this->forum = $forum;
        $this->setEvent($event);
        $this->url = $url;
        $this->secretToken = $secretToken;
    }

    public function getId(): Uuid {
        return $this->id;
    }

    public function getForum(): Forum {
        return $this->forum;
    }

    public function getEvent(): string {
        return $this->event;
    }

    public function setEvent(string $event): void {
        if (!\in_array($event, self::EVENTS, true)) {
            throw new \DomainException(\sprintf('Invalid event "%s"', $event));
        }

        $this->event = $event;
    }

    public function getUrl(): string {
        return $this->url;
    }

    public function setUrl(string $url): void {
        $this->url = $url;
    }

    public function getSecretToken(): ?string {
        return $this->secretToken;
    }

    public function setSecretToken(?string $secretToken): void {
        $this->secretToken = $secretToken;
    }
}
